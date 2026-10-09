<?php

use App\Services\Calculators\DiscountCalculator;
use App\Services\Calculators\FreelanceRateCalculator;
use App\Services\Calculators\InvoiceCalculator;
use App\Services\Calculators\ProfitCalculator;
use App\Services\Qr\QrContent;

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

test('successive discounts apply to the remaining price', function () {
    $result = (new DiscountCalculator)->calculate([
        'original_price' => '100.00',
        'discount_type' => 'percent',
        'discount_percent' => '20',
        'successive_percents' => ['10'],
        'currency' => 'EUR',
    ]);

    expect($result['sale_price'])->toBe('72.00')
        ->and($result['savings'])->toBe('28.00')
        ->and($result['effective_discount'])->toBe('28.00');
});

test('a fixed discount is removed before a later percentage', function () {
    $result = (new DiscountCalculator)->calculate([
        'original_price' => '50.00',
        'discount_type' => 'amount',
        'discount_amount' => '10.00',
        'successive_percents' => ['25'],
    ]);

    expect($result['sale_price'])->toBe('30.00')
        ->and($result['effective_discount'])->toBe('40.00');
});

test('freelance rates cover the income target plus expenses', function () {
    $result = (new FreelanceRateCalculator)->calculate([
        'income_target' => '60000.00',
        'annual_expenses' => '6000.00',
        'hours_per_day' => '5',
        'days_per_week' => 5,
        'weeks_per_year' => 48,
        'unpaid_leave_days' => 10,
        'currency' => 'EUR',
    ]);

    expect($result['available_days'])->toBe(230)
        ->and($result['required_revenue'])->toBe('66000.00')
        ->and($result['hourly_rate'])->toBe('57.39')
        ->and($result['daily_rate'])->toBe('286.96');
});

test('freelance rates reject leave that removes every working day', function () {
    expect(fn () => (new FreelanceRateCalculator)->calculate([
        'income_target' => '1000.00',
        'hours_per_day' => '5',
        'days_per_week' => 5,
        'weeks_per_year' => 1,
        'unpaid_leave_days' => 5,
    ]))->toThrow(InvalidArgumentException::class);
});

test('qr content builds a wifi payload without dropping escaped characters', function () {
    $payload = (new QrContent)->payload([
        'type' => 'wifi',
        'wifi_ssid' => 'Cafe:Net',
        'wifi_password' => 'p@ss;word',
        'wifi_security' => 'WPA',
        'wifi_hidden' => false,
    ]);

    expect($payload)->toBe('WIFI:T:WPA;S:Cafe\\:Net;P:p@ss\\;word;H:false;;');
});
