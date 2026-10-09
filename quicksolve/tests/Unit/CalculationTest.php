<?php

use App\Services\Calculators\InvoiceCalculator;
use App\Services\Calculators\ProfitCalculator;

test('profit calculator separates percentage and fixed fees', function () {
    $result = (new ProfitCalculator)->calculate([
        'selling_price' => '49.00',
        'product_cost' => '18.00',
        'shipping_cost' => '3.50',
        'packaging_cost' => '1.20',
        'marketplace_fee_percent' => '15',
        'marketplace_fee_fixed' => '0.30',
        'processing_fee_percent' => '2.9',
        'processing_fee_fixed' => '0.25',
        'currency' => 'EUR',
    ]);

    expect($result['marketplace_fee'])->toBe('7.65')
        ->and($result['processing_fee'])->toBe('1.67')
        ->and($result['total_cost'])->toBe('32.02')
        ->and($result['profit'])->toBe('16.98')
        ->and($result['profit_margin'])->toBe('34.65')
        ->and($result['markup'])->toBe('53.03')
        ->and($result['break_even_price'])->toBe('28.32');
});

test('profit calculator rejects a selling price that is not a decimal amount', function () {
    expect(fn () => (new ProfitCalculator)->calculate([
        'selling_price' => '-1',
        'product_cost' => '1.00',
    ]))->toThrow(InvalidArgumentException::class);
});

test('break-even is unavailable when percentage fees consume the whole price', function () {
    $result = (new ProfitCalculator)->calculate([
        'selling_price' => '10.00',
        'product_cost' => '1.00',
        'marketplace_fee_percent' => '80',
        'processing_fee_percent' => '20',
    ]);

    expect($result['break_even_price'])->toBeNull();
});

test('invoice totals apply one tax rate to the subtotal', function () {
    $result = (new InvoiceCalculator)->calculate([
        'currency' => 'EUR',
        'tax_rate' => '20',
        'items' => [
            ['description' => 'Design', 'quantity' => '1.5', 'unit_price' => '10.00'],
            ['description' => 'Hosting', 'quantity' => '1', 'unit_price' => '5.00'],
        ],
    ]);

    expect($result['subtotal'])->toBe('20.00')
        ->and($result['tax'])->toBe('4.00')
        ->and($result['total'])->toBe('24.00');
});
