<?php

namespace App\Services\Billing;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;

class SubscriptionSyncer
{
    public function __construct(
        private readonly PlanPriceResolver $prices,
        private readonly PlanResolver $plans,
    ) {}

    public function refreshPaidAccess(User $user, PlanResolver $plans): void
    {
        $this->syncIfLocallyFree($user, $plans);
        $this->reconcileOverlappingPlans($user);
    }

    public function syncIfLocallyFree(User $user, PlanResolver $plans): void
    {
        if ($plans->key($user) !== 'free') {
            return;
        }

        $this->syncUser($user);
        $user->refresh();
        $user->unsetRelation('subscriptions');
    }

    public function reconcileOverlappingPlans(User $user): void
    {
        $user->unsetRelation('subscriptions');
        $keep = $this->plans->currentSubscription($user);

        if (! $keep) {
            return;
        }

        foreach ($user->subscriptions as $subscription) {
            if (! $subscription instanceof Subscription || $subscription->is($keep)) {
                continue;
            }

            if (in_array($subscription->stripe_status, ['canceled', 'incomplete_expired'], true)) {
                continue;
            }

            $plan = $this->plans->keyForPrice($this->plans->priceIdFor($subscription));

            if (! in_array($plan, ['pro', 'business'], true)) {
                continue;
            }

            $this->stopSubscription($subscription);
        }
    }

    public function syncUser(User $user, ?string $checkoutSessionId = null): void
    {
        $secret = config('cashier.secret');

        if (! is_string($secret) || $secret === '') {
            return;
        }

        try {
            if (is_string($checkoutSessionId) && $checkoutSessionId !== '') {
                $this->syncFromCheckoutSession($user, $checkoutSessionId);
            } else {
                $this->syncFromCustomer($user);
            }

            $this->reconcileOverlappingPlans($user);
        } catch (ApiErrorException $exception) {
            Log::warning('Stripe subscription sync failed.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);
        }
    }

    public function syncFromCustomerId(string $customerId): void
    {
        $user = User::query()->where('stripe_id', $customerId)->first();

        if (! $user) {
            try {
                $customer = Cashier::stripe()->customers->retrieve($customerId);
                $email = is_string($customer->email ?? null) ? $customer->email : null;
                $user = is_string($email) && $email !== ''
                    ? User::query()->where('email', $email)->first()
                    : null;

                if ($user && ! $user->stripe_id) {
                    $user->forceFill(['stripe_id' => $customerId])->save();
                }
            } catch (ApiErrorException $exception) {
                Log::warning('Stripe customer lookup failed during subscription sync.', [
                    'exception' => $exception::class,
                ]);
            }
        }

        if (! $user) {
            return;
        }

        $this->syncUser($user);
    }

    public function syncKnownPlanPrices(): int
    {
        $imported = 0;

        foreach (['pro', 'business'] as $plan) {
            foreach ($this->prices->knownIds($plan) as $priceId) {
                $subscriptions = Cashier::stripe()->subscriptions->all([
                    'price' => $priceId,
                    'status' => 'all',
                    'limit' => 100,
                ]);

                foreach ($subscriptions->data as $subscription) {
                    $customerId = is_string($subscription->customer) ? $subscription->customer : $subscription->customer->id;
                    $customer = Cashier::stripe()->customers->retrieve($customerId);
                    $user = User::query()->where('email', $customer->email)->first();

                    if (! $user) {
                        continue;
                    }

                    if (! $user->stripe_id) {
                        $user->forceFill(['stripe_id' => $customerId])->save();
                    }

                    $this->import($user, $subscription);
                    $imported++;
                }
            }
        }

        return $imported;
    }

    private function syncFromCheckoutSession(User $user, string $sessionId): void
    {
        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId, [
            'expand' => ['subscription', 'customer'],
        ]);

        if (! $this->sessionBelongsToUser($user, $session)) {
            return;
        }

        $customerId = $this->customerId($session->customer);

        if ($customerId && ! $user->stripe_id) {
            $user->forceFill(['stripe_id' => $customerId])->save();
        }

        if (($session->mode ?? null) !== 'subscription' || ! $session->subscription) {
            return;
        }

        $subscription = $session->subscription instanceof StripeSubscription
            ? $session->subscription
            : Cashier::stripe()->subscriptions->retrieve((string) $session->subscription);

        $this->import($user, $subscription);
    }

    private function syncFromCustomer(User $user): void
    {
        if (! $user->stripe_id) {
            $this->attachCustomerByEmail($user);
        }

        if (! $user->stripe_id) {
            return;
        }

        $subscriptions = Cashier::stripe()->subscriptions->all([
            'customer' => $user->stripe_id,
            'status' => 'all',
            'limit' => 20,
        ]);

        foreach ($subscriptions->data as $subscription) {
            $this->import($user, $subscription);
        }
    }

    private function attachCustomerByEmail(User $user): void
    {
        $customers = Cashier::stripe()->customers->all([
            'email' => $user->email,
            'limit' => 10,
        ]);

        if (count($customers->data) === 0) {
            return;
        }

        if (count($customers->data) === 1) {
            $user->forceFill(['stripe_id' => $customers->data[0]->id])->save();

            return;
        }

        foreach ($customers->data as $customer) {
            $subscriptions = Cashier::stripe()->subscriptions->all([
                'customer' => $customer->id,
                'status' => 'active',
                'limit' => 1,
            ]);

            if (count($subscriptions->data) > 0) {
                $user->forceFill(['stripe_id' => $customer->id])->save();

                return;
            }
        }
    }

    public function import(User $user, StripeSubscription $stripeSubscription): void
    {
        if ($stripeSubscription->status === StripeSubscription::STATUS_INCOMPLETE_EXPIRED) {
            $existing = $user->subscriptions()->where('stripe_id', $stripeSubscription->id)->first();

            if ($existing) {
                $existing->items()->delete();
                $existing->delete();
            }

            return;
        }

        $items = $stripeSubscription->items->data ?? [];

        if ($items === []) {
            return;
        }

        $firstItem = $items[0];
        $singlePrice = count($items) === 1;
        $planMeta = is_object($firstItem->price ?? null)
            ? ($firstItem->price->metadata['quicksolve_plan'] ?? null)
            : null;

        if (is_string($planMeta) && isset($firstItem->price->id)) {
            $this->prices->remember($planMeta, (string) $firstItem->price->id);
        }
        $trialEndsAt = $stripeSubscription->trial_end
            ? Carbon::createFromTimestamp($stripeSubscription->trial_end)
            : null;

        $endsAt = null;

        if ($stripeSubscription->cancel_at_period_end) {
            $endsAt = $stripeSubscription->current_period_end
                ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                : $trialEndsAt;
        } elseif ($stripeSubscription->cancel_at || $stripeSubscription->canceled_at) {
            $endsAt = Carbon::createFromTimestamp($stripeSubscription->cancel_at ?? $stripeSubscription->canceled_at);
        }

        $subscription = $user->subscriptions()->updateOrCreate(
            ['stripe_id' => $stripeSubscription->id],
            [
                'type' => $stripeSubscription->metadata['type'] ?? $stripeSubscription->metadata['name'] ?? 'default',
                'stripe_status' => $stripeSubscription->status,
                'stripe_price' => $singlePrice ? $firstItem->price->id : null,
                'quantity' => $singlePrice ? ($firstItem->quantity ?? 1) : null,
                'trial_ends_at' => $trialEndsAt,
                'ends_at' => $endsAt,
            ],
        );

        foreach ($items as $item) {
            $product = $item->price->product;

            $subscription->items()->updateOrCreate(
                ['stripe_id' => $item->id],
                [
                    'stripe_product' => is_string($product) ? $product : (string) $product->id,
                    'stripe_price' => $item->price->id,
                    'quantity' => $item->quantity ?? null,
                ],
            );
        }
    }

    private function stopSubscription(Subscription $subscription): void
    {
        try {
            $subscription->cancelNow();
        } catch (\Throwable $exception) {
            Log::warning('Could not cancel an overlapping Stripe subscription.', [
                'subscription_id' => $subscription->stripe_id,
                'exception' => $exception::class,
            ]);

            $subscription->forceFill([
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ])->save();
        }
    }

    private function sessionBelongsToUser(User $user, Session $session): bool
    {
        if ((string) $session->client_reference_id === (string) $user->id) {
            return true;
        }

        $customerId = $this->customerId($session->customer);

        if ($user->stripe_id && $customerId && $user->stripe_id === $customerId) {
            return true;
        }

        $email = null;

        if (is_object($session->customer) && isset($session->customer->email)) {
            $email = $session->customer->email;
        }

        $email ??= $session->customer_details->email ?? $session->customer_email ?? null;

        return is_string($email) && strcasecmp($email, $user->email) === 0;
    }

    private function customerId(mixed $customer): ?string
    {
        if (is_string($customer) && $customer !== '') {
            return $customer;
        }

        if (is_object($customer) && isset($customer->id) && is_string($customer->id)) {
            return $customer->id;
        }

        return null;
    }
}
