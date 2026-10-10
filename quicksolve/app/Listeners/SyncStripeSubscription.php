<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Billing\SubscriptionSyncer;
use Laravel\Cashier\Events\WebhookReceived;

class SyncStripeSubscription
{
    public function __construct(private readonly SubscriptionSyncer $syncer) {}

    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'] ?? null;

        if (! in_array($type, ['checkout.session.completed', 'customer.subscription.created', 'customer.subscription.updated'], true)) {
            return;
        }

        $object = $payload['data']['object'] ?? [];
        $customerId = $object['customer'] ?? null;

        if ($type === 'checkout.session.completed') {
            if (($object['mode'] ?? null) !== 'subscription') {
                return;
            }

            $reference = $object['client_reference_id'] ?? null;

            if (is_numeric($reference) && ($user = User::query()->find($reference))) {
                $this->syncer->syncUser($user, is_string($object['id'] ?? null) ? $object['id'] : null);

                return;
            }
        }

        if (is_string($customerId) && $customerId !== '') {
            $this->syncer->syncFromCustomerId($customerId);
        }
    }
}
