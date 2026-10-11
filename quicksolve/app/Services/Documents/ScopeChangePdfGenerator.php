<?php

namespace App\Services\Documents;

use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use InvalidArgumentException;

class ScopeChangePdfGenerator
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function download(array $input): Response
    {
        $amount = Money::parse((string) $input['extra_amount']);

        if ($amount < 0) {
            throw new InvalidArgumentException('Extra cost cannot be negative.');
        }

        $filename = 'scope-change-'.str((string) $input['project_name'])->slug().'.pdf';

        return Pdf::loadView('scope-changes.pdf', [
            'note' => [
                ...$input,
                'extra_amount_display' => Money::formatWithCurrency($amount, (string) $input['currency']),
                'hold_until_confirmed' => (bool) ($input['hold_until_confirmed'] ?? true),
            ],
        ])->download($filename);
    }
}
