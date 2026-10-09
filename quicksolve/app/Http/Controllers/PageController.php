<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Inertia\Response;

class PageController extends Controller
{
    public function pricing(): Response
    {
        $plans = collect(config('quicksolve.plans'))->map(fn (array $plan, string $key) => [
            'key' => $key,
            'name' => $plan['name'],
            'monthly_price' => $plan['monthly_price'],
            'formatted_price' => $plan['monthly_price'] === 0 ? '€0' : '€'.number_format($plan['monthly_price'] / 100, 0),
            'description' => $plan['description'],
            'features' => $plan['features'],
            'checkout' => in_array($key, ['pro', 'business'], true),
        ])->values();

        return Seo::page('Pricing', [
            'plans' => $plans,
            'billingNote' => 'Prices are in euros and billed monthly through Stripe. You can cancel from the billing portal. Access continues until the end of the period you already paid for. Digital template purchases are billed separately.',
        ], 'Pricing', 'Compare Free, Pro, and Business plans for QuickSolve tools and downloads.', '/pricing');
    }

    public function about(): Response
    {
        return Seo::page('About', [], 'About', 'QuickSolve is a small set of practical tools and templates for people running a business without a large software stack.', '/about');
    }

    public function privacy(): Response
    {
        return Seo::page('Privacy', [], 'Privacy policy', 'How QuickSolve handles account data, payments, downloads, and usage records.', '/privacy');
    }

    public function terms(): Response
    {
        return Seo::page('Terms', [], 'Terms of service', 'The terms that apply when you use QuickSolve tools, subscriptions, and digital downloads.', '/terms');
    }
}
