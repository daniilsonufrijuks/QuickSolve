<?php

return [

    'currencies' => ['EUR', 'USD', 'GBP'],

    'guest_generator_daily_limit' => (int) env('GUEST_GENERATOR_DAILY_LIMIT', 3),

    'premium_trial_uses' => (int) env('PREMIUM_TRIAL_USES', 5),

    'usage_retention_days' => (int) env('USAGE_RETENTION_DAYS', 90),

    'plans' => [
        'free' => [
            'name' => 'Free',
            'monthly_price' => 0,
            'stripe_price_id' => null,
            'generator_limit' => 5,
            'description' => 'The calculators and a small monthly allowance for product descriptions.',
            'features' => [
                'Profit, discount, and freelance rate calculators',
                'Invoice generator with PDF export',
                'QR codes for links, Wi-Fi, contacts, and text',
                '5 trial uses of Pro tools',
                '5 product descriptions per month',
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'monthly_price' => 500,
            'stripe_price_id' => env('STRIPE_PRO_PRICE_ID'),
            'generator_limit' => 100,
            'description' => 'A higher writing allowance and long-form descriptions for active sellers.',
            'features' => [
                'Everything in Free',
                '100 product descriptions and email drafts per month',
                'Long descriptions',
                'Branded QR colors and freelance rate reports',
                'PDF viewer, image tools, CV, quotes, cover letters, and cash runway',
                'Invoice and quote accent colour on PDFs',
                'Business card maker, timesheet exports, and email templates with DeepSeek drafts',
            ],
        ],
        'business' => [
            'name' => 'Business',
            'monthly_price' => 1000,
            'stripe_price_id' => env('STRIPE_BUSINESS_PRICE_ID'),
            'generator_limit' => 500,
            'description' => 'Unbranded documents, a custom footer, and CSV exports for a busier catalog.',
            'features' => [
                'Everything in Pro',
                '500 product descriptions and email drafts per month',
                'Unbranded invoice and quote PDFs (no QuickSolve line)',
                'Custom document footer',
                'CSV export of saved documents and usage',
            ],
        ],
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'deepseek'),
        'api_key' => env('AI_PROVIDER_API_KEY'),
        'base_url' => env('AI_PROVIDER_BASE_URL', 'https://api.deepseek.com'),
        'model' => env('AI_PROVIDER_MODEL', 'deepseek-chat'),
        'timeout' => (int) env('AI_PROVIDER_TIMEOUT', 30),
    ],

];
