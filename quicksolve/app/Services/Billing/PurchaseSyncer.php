<?php

namespace App\Services\Billing;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class PurchaseSyncer
{
    public function __construct(private readonly PurchaseFulfiller $fulfiller) {}

    public function syncUser(User $user, ?string $checkoutSessionId = null): void
    {
        $secret = config('cashier.secret');

        if (! is_string($secret) || $secret === '') {
            return;
        }

        try {
            if (is_string($checkoutSessionId) && $checkoutSessionId !== '') {
                $this->syncFromCheckoutSession($user, $checkoutSessionId);
            }

            $this->syncPendingPurchases($user);
            $this->syncFromCustomer($user);
        } catch (ApiErrorException $exception) {
            Log::warning('Stripe purchase sync failed.', [
                'user_id' => $user->id,
                'exception' => $exception::class,
            ]);
        }
    }

    public function syncPendingForUsersWithStripe(): int
    {
        $checked = 0;

        User::query()
            ->whereNotNull('stripe_id')
            ->whereHas('purchases', fn ($query) => $query->where('status', PurchaseStatus::Pending))
            ->each(function (User $user) use (&$checked) {
                $this->syncUser($user);
                $checked++;
            });

        return $checked;
    }

    private function syncFromCheckoutSession(User $user, string $sessionId): void
    {
        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        if (! $this->sessionBelongsToUser($user, $session)) {
            return;
        }

        $this->fulfiller->fulfillFromStripeSession($session->toArray());
    }

    private function syncPendingPurchases(User $user): void
    {
        $purchases = Purchase::query()
            ->where('user_id', $user->id)
            ->where('status', PurchaseStatus::Pending)
            ->whereNotNull('payment_reference')
            ->get();

        foreach ($purchases as $purchase) {
            $session = Cashier::stripe()->checkout->sessions->retrieve((string) $purchase->payment_reference);

            if (! $this->sessionBelongsToUser($user, $session)) {
                continue;
            }

            $this->fulfiller->fulfillFromStripeSession($session->toArray());
        }
    }

    private function syncFromCustomer(User $user): void
    {
        if (! $user->stripe_id) {
            return;
        }

        $sessions = Cashier::stripe()->checkout->sessions->all([
            'customer' => $user->stripe_id,
            'limit' => 20,
        ]);

        foreach ($sessions->data as $session) {
            if (($session->mode ?? null) !== 'payment') {
                continue;
            }

            $this->fulfiller->fulfillFromStripeSession($session->toArray());
        }
    }

    private function sessionBelongsToUser(User $user, Session $session): bool
    {
        if ((string) $session->client_reference_id === (string) $user->id) {
            return true;
        }

        $customerId = is_string($session->customer) ? $session->customer : ($session->customer->id ?? null);

        if ($user->stripe_id && is_string($customerId) && $user->stripe_id === $customerId) {
            return true;
        }

        $purchaseId = $session->metadata['purchase_id'] ?? null;

        if (is_scalar($purchaseId) && Purchase::query()->whereKey($purchaseId)->where('user_id', $user->id)->exists()) {
            return true;
        }

        $email = $session->customer_details->email ?? $session->customer_email ?? null;

        return is_string($email) && strcasecmp($email, $user->email) === 0;
    }
}
