<?php

namespace App\Listeners;

use App\Enums\PurchaseStatus;
use App\Mail\PurchaseReceipt;
use App\Models\Purchase;
use App\Models\UsageEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Events\WebhookReceived;

class FulfillTemplatePurchase
{
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;

        if (($payload['type'] ?? null) !== 'checkout.session.completed') {
            return;
        }

        $session = $payload['data']['object'] ?? null;

        if (! is_array($session) || ($session['mode'] ?? null) !== 'payment' || ($session['payment_status'] ?? null) !== 'paid') {
            return;
        }

        $purchaseId = $session['metadata']['purchase_id'] ?? null;

        if (! is_scalar($purchaseId)) {
            return;
        }

        DB::transaction(function () use ($session, $purchaseId) {
            $purchase = Purchase::query()->lockForUpdate()->find($purchaseId);

            if (! $purchase || $purchase->status === PurchaseStatus::Paid) {
                return;
            }

            $amount = $session['amount_total'] ?? null;
            $currency = strtoupper((string) ($session['currency'] ?? ''));

            if ((int) $amount !== (int) $purchase->amount || $currency !== strtoupper($purchase->currency)) {
                Log::warning('Stripe checkout did not match the stored purchase.', [
                    'purchase_id' => $purchase->id,
                ]);

                return;
            }

            $purchase->update([
                'status' => PurchaseStatus::Paid,
                'paid_at' => now(),
                'payment_reference' => is_string($session['id'] ?? null) ? $session['id'] : null,
            ]);

            UsageEvent::query()->create([
                'user_id' => $purchase->user_id,
                'tool_slug' => 'templates',
                'usage_type' => 'purchase_completed',
                'metadata' => ['purchase_id' => $purchase->id],
            ]);

            $purchase->load('user', 'template');

            if ($purchase->user) {
                Mail::to($purchase->user)->queue(new PurchaseReceipt($purchase));
            }
        });
    }
}
