<?php

namespace App\Services\Calculators;

use App\Support\Money;
use InvalidArgumentException;

class InvoiceCalculator
{
    /**
     * Line totals are quantity × unit price, rounded half-up to the minor unit.
     * One tax rate is applied to the subtotal. This does not calculate jurisdiction-specific tax.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $currency = strtoupper((string) ($input['currency'] ?? 'EUR'));

        if (! in_array($currency, ['EUR', 'USD', 'GBP'], true)) {
            throw new InvalidArgumentException('Choose a supported currency.');
        }

        $items = $input['items'] ?? [];

        if (! is_array($items) || $items === []) {
            throw new InvalidArgumentException('Add at least one line item.');
        }

        $taxRate = Money::parsePercentToBasisPoints((string) ($input['tax_rate'] ?? '0'));
        $lines = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $description = trim((string) ($item['description'] ?? ''));

            if ($description === '') {
                throw new InvalidArgumentException('Each line item needs a description.');
            }

            $quantity = Money::quantityHundredths((string) ($item['quantity'] ?? ''));
            $unitPrice = Money::parse((string) ($item['unit_price'] ?? ''));
            $lineTotal = Money::lineTotal($quantity, $unitPrice);
            $subtotal += $lineTotal;

            $lines[] = [
                'description' => $description,
                'quantity' => number_format($quantity / 100, 2, '.', ''),
                'unit_price' => Money::format($unitPrice),
                'line_total' => Money::format($lineTotal),
            ];
        }

        $tax = Money::percentOf($subtotal, $taxRate);

        return [
            'currency' => $currency,
            'items' => $lines,
            'subtotal' => Money::format($subtotal),
            'tax_rate' => Money::formatBasisPoints($taxRate),
            'tax' => Money::format($tax),
            'total' => Money::format($subtotal + $tax),
            'subtotal_minor' => $subtotal,
            'tax_minor' => $tax,
            'total_minor' => $subtotal + $tax,
        ];
    }
}
