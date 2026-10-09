<?php

namespace App\Services\Calculators;

use App\Support\Money;
use InvalidArgumentException;

class DiscountCalculator
{
    /**
     * A percentage discount is taken from the price remaining at that step.
     * A fixed discount is subtracted once, before any successive percentages.
     * Successive percentages are not added together.
     *
     * @param  array{
     *     original_price: string,
     *     discount_type?: string,
     *     discount_percent?: string,
     *     discount_amount?: string,
     *     successive_percents?: array<int, string>,
     *     currency?: string
     * }  $input
     * @return array<string, mixed>
     */
    public function calculate(array $input): array
    {
        $currency = strtoupper($input['currency'] ?? 'EUR');

        if (! in_array($currency, ['EUR', 'USD', 'GBP'], true)) {
            throw new InvalidArgumentException('Choose a supported currency.');
        }

        $original = Money::parse($input['original_price']);
        $type = $input['discount_type'] ?? 'percent';
        $price = $original;

        if ($type === 'amount') {
            $price = max(0, $price - Money::parse($input['discount_amount'] ?? ''));
        } elseif ($type === 'percent') {
            $price = $this->applyPercent($price, $input['discount_percent'] ?? '0');
        } else {
            throw new InvalidArgumentException('Choose a percentage or a fixed discount.');
        }

        foreach ($input['successive_percents'] ?? [] as $percent) {
            if (trim((string) $percent) === '') {
                continue;
            }

            $price = $this->applyPercent($price, (string) $percent);
        }

        $savings = $original - $price;

        return [
            'currency' => $currency,
            'original_price' => Money::format($original),
            'sale_price' => Money::format($price),
            'savings' => Money::format($savings),
            'effective_discount' => Money::formatBasisPoints((int) Money::ratioBasisPoints($savings, $original)),
        ];
    }

    private function applyPercent(int $price, string $percent): int
    {
        $rate = Money::parsePercentToBasisPoints($percent);

        return $price - Money::percentOf($price, $rate);
    }
}
