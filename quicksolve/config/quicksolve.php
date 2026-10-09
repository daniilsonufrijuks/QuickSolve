<?php

return [

    'currencies' => ['EUR', 'USD', 'GBP'],

    'guest_generator_daily_limit' => (int) env('GUEST_GENERATOR_DAILY_LIMIT', 3),

    'usage_retention_days' => (int) env('USAGE_RETENTION_DAYS', 90),

    'plans' => [
        'free' => [
            'name' => 'Free',
            'monthly_price' => 0,
            'stripe_price_id' => null,
            'generator_limit' => 5,
            'description' => 'The calculators and a small monthly allowance for product descriptions.',
            'features' => [
                'Profit margin calculator',
                'Invoice generator with PDF export',
                '5 product descriptions per month',
                'Account storage for invoices',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'monthly_price' => 900,
            'stripe_price_id' => env('STRIPE_PRO_PRICE_ID'),
            'generator_limit' => 100,
            'description' => 'A higher writing allowance and long-form descriptions for active sellers.',
            'features' => [
                'Everything in Free',
                '100 product descriptions per month',
                'Long descriptions',
                'Premium tools as they are published',
            ],
        ],
        'business' => [
            'name' => 'Business',
            'monthly_price' => 1900,
            'stripe_price_id' => env('STRIPE_BUSINESS_PRICE_ID'),
            'generator_limit' => 500,
            'description' => 'The same tools with room for a busier catalog and more exports.',
            'features' => [
                'Everything in Pro',
                '500 product descriptions per month',
                'Long descriptions',
                'Higher limits for growing teams',
            ],
        ],
    ],

    'ai' => [
        'api_key' => env('AI_PROVIDER_API_KEY'),
        'base_url' => env('AI_PROVIDER_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('AI_PROVIDER_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('AI_PROVIDER_TIMEOUT', 20),
    ],

];
