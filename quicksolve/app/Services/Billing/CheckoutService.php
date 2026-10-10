<?php

namespace App\Services\Billing;

use App\Exceptions\BillingNotConfiguredException;
use App\Models\Purchase;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    public function __construct(private readonly PlanPriceResolver $prices) {}

    public function subscriptionCheckout(User $user, string $plan): string
    {
        $this->assertConfigured();

        if (! in_array($plan, ['pro', 'business'], true)) {
            throw new BillingNotConfiguredException('Choose the Pro or Business plan.');
        }

        $priceId = $this->prices->ensure($plan);

        try {
            return $user->newSubscription('default', $priceId)->checkout([
                'success_url' => route('dashboard.subscription', ['status' => 'processing']).'&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('pricing'),
                'client_reference_id' => (string) $user->id,
                'metadata' => [
                    'plan' => $plan,
                    'user_id' => (string) $user->id,
                ],
            ])->asStripeCheckoutSession()->url;
        } catch (BillingNotConfiguredException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::warning('Subscription checkout could not be created.', [
                'plan' => $plan,
                'exception' => $exception::class,
            ]);

            throw new BillingNotConfiguredException('Checkout could not be started. Confirm Stripe test mode is configured and try again from Admin → Billing.');
        }
    }

    public function templateCheckout(User $user, Template $template): string
    {
        $this->assertConfigured();

        if (! $template->is_published || ! $template->private_file_path) {
            throw new BillingNotConfiguredException('This template is not available for purchase.');
        }

        if (strtoupper($template->currency) !== strtoupper((string) config('cashier.currency'))) {
            throw new BillingNotConfiguredException('This template currency does not match the configured billing currency.');
        }

        $alreadyOwned = Purchase::query()
            ->where('user_id', $user->id)
            ->where('template_id', $template->id)
            ->where('status', 'paid')
            ->exists();

        if ($alreadyOwned) {
            throw new BillingNotConfiguredException('You already own this template. Download it from your account.');
        }

        $purchase = Purchase::query()->create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'amount' => $template->price,
            'currency' => strtoupper($template->currency),
            'payment_provider' => 'stripe',
            'status' => 'pending',
        ]);

        try {
            return $user->checkoutCharge(
                $template->price,
                $template->name,
                1,
                [
                    'success_url' => route('dashboard.purchases', ['status' => 'processing']),
                    'cancel_url' => route('templates.show', ['slug' => $template->slug]),
                    'metadata' => [
                        'purchase_id' => (string) $purchase->id,
                        'template_id' => (string) $template->id,
                    ],
                ],
            )->asStripeCheckoutSession()->url;
        } catch (BillingNotConfiguredException $exception) {
            $purchase->update(['status' => 'failed']);

            throw $exception;
        } catch (\Throwable $exception) {
            $purchase->update(['status' => 'failed']);

            Log::warning('Template checkout could not be created.', [
                'purchase_id' => $purchase->id,
                'exception' => $exception::class,
            ]);

            throw new BillingNotConfiguredException('Checkout could not be started. Confirm Stripe test mode is configured and try again.');
        }
    }

    public function assertConfigured(): void
    {
        $secret = config('cashier.secret');

        if (! is_string($secret) || $secret === '') {
            throw new BillingNotConfiguredException;
        }
    }
}
