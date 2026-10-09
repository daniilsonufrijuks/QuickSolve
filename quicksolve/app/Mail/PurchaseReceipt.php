<?php

namespace App\Mail;

use App\Models\Purchase;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Purchase $purchase) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your QuickSolve download is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.purchase-receipt',
            with: [
                'amount' => Money::formatWithCurrency($this->purchase->amount, $this->purchase->currency),
            ],
        );
    }
}
