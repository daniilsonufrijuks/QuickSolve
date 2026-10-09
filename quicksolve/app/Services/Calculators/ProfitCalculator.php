<?php

namespace App\Services\Calculators;

use App\Support\Money;
use InvalidArgumentException;

class ProfitCalculator
{
    /**
     * Fees are split into a percentage of the selling price and a fixed amount.
     * Percentage fees use half-up rounding to the nearest minor currency unit.
     * Profit margin is profit divided by selling price. Markup is profit divided by total cost.
     * Break-even price solves selling = fixed costs + selling * combined percentage rate.
     *
     * @param  array{
     *     selling_price: string,
     *     product_cost: string,
     *     shipping_cost?: string,
     *     packaging_cost?: string,
     *     marketplace_fee_percent?: string,
     *     marketplace_fee_fixed?: string,
     *     processing_fee_percent?: string,
     *     processing_fee_fixed?: string,
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

        $selling = Money::parse($input['selling_price']);
        $product = Money::parse($input['product_cost']);
        $shipping = Money::optional($input['shipping_cost'] ?? '0');
        $packaging = Money::optional($input['packaging_cost'] ?? '0');
        $marketplaceRate = Money::parsePercentToBasisPoints($input['marketplace_fee_percent'] ?? '0');
        $marketplaceFixed = Money::optional($input['marketplace_fee_fixed'] ?? '0');
        $processingRate = Money::parsePercentToBasisPoints($input['processing_fee_percent'] ?? '0');
        $processingFixed = Money::optional($input['processing_fee_fixed'] ?? '0');

        $marketplaceFee = Money::percentOf($selling, $marketplaceRate) + $marketplaceFixed;
        $processingFee = Money::percentOf($selling, $processingRate) + $processingFixed;
        $totalCost = $product + $shipping + $packaging + $marketplaceFee + $processingFee;
        $profit = $selling - $totalCost;
        $margin = Money::ratioBasisPoints($profit, $selling);
        $markup = Money::ratioBasisPoints($profit, $totalCost);

        $fixedCosts = $product + $shipping + $packaging + $marketplaceFixed + $processingFixed;
        $combinedRate = $marketplaceRate + $processingRate;
        $breakEven = null;

        if ($combinedRate < 10000) {
            $denominator = 10000 - $combinedRate;
            $breakEven = intdiv(($fixedCosts * 10000) + intdiv($denominator, 2), $denominator);
        }

        return [
            'currency' => $currency,
            'selling_price' => Money::format($selling),
            'product_cost' => Money::format($product),
            'shipping_cost' => Money::format($shipping),
            'packaging_cost' => Money::format($packaging),
            'marketplace_fee' => Money::format($marketplaceFee),
            'processing_fee' => Money::format($processingFee),
            'total_cost' => Money::format($totalCost),
            'profit' => Money::format($profit),
            'profit_margin' => $margin === null ? null : Money::formatBasisPoints($margin),
            'markup' => $markup === null ? null : Money::formatBasisPoints($markup),
            'break_even_price' => $breakEven === null ? null : Money::format($breakEven),
            'fee_basis' => [
                'marketplace' => 'percentage of selling price, plus a fixed amount',
                'processing' => 'percentage of selling price, plus a fixed amount',
            ],
        ];
    }
}
