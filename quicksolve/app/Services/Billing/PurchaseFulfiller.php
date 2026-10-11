<?php

namespace App\Services\Billing;

use App\Enums\PurchaseStatus;
use App\Mail\PurchaseReceipt;
use App\Models\Purchase;
use App\Models\UsageEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PurchaseFulfiller
{
    /**
     * @param  array<string, mixed>  $session
     */
    public function fulfillFromStripeSession(array $session): bool
    {
        if (($session['mode'] ?? null) !== 'payment') {
            return false;
        }

        $paymentStatus = $session['payment_status'] ?? null;

        if (! in_array($paymentStatus, ['paid', 'no_payment_required'], true)) {
            return false;
        }

        $purchaseId = $session['metadata']['purchase_id'] ?? null;

        if (! is_scalar($purchaseId)) {
            return false;
        }

        return (bool) DB::transaction(function () use ($session, $purchaseId) {
            $purchase = Purchase::query()->lockForUpdate()->find($purchaseId);

            if (! $purchase || $purchase->status === PurchaseStatus::Paid) {
                return $purchase?->status === PurchaseStatus::Paid;
            }

            $amount = $session['amount_total'] ?? null;
            $currency = strtoupper((string) ($session['currency'] ?? ''));

            if ((int) $amount !== (int) $purchase->amount || $currency !== strtoupper($purchase->currency)) {
                Log::warning('Stripe checkout did not match the stored purchase.', [
                    'purchase_id' => $purchase->id,
                ]);

                return false;
            }

            $purchase->update([
                'status' => PurchaseStatus::Paid,
                'paid_at' => now(),
                'payment_reference' => is_string($session['id'] ?? null) ? $session['id'] : $purchase->payment_reference,
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

            return true;
        });
    }
}
