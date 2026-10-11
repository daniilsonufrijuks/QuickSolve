<?php

namespace App\Listeners;

use App\Services\Billing\PurchaseFulfiller;
use Laravel\Cashier\Events\WebhookReceived;

class FulfillTemplatePurchase
{
    public function __construct(private readonly PurchaseFulfiller $fulfiller) {}

    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? null;

        if (! in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return;
        }

        $session = $payload['data']['object'] ?? null;

        if (! is_array($session)) {
            return;
        }

        $this->fulfiller->fulfillFromStripeSession($session);
    }
}
