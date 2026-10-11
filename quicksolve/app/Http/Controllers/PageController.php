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

        $title = 'Pricing';
        $description = 'Free is €0. Pro is €5 per month. Business is €10 per month. Five Pro-tool uses are free. Checkout uses the server price; a success URL does not turn Pro on.';

        return Seo::page('Pricing', [
            'plans' => $plans,
            'billingNote' => 'Prices are in euros and billed monthly through Stripe. You can cancel from the billing portal. Access continues until the end of the period you already paid for. Digital template purchases are billed separately.',
        ], $title, $description, '/pricing', [
            'jsonLd' => Seo::graph([
                Seo::organization(),
                Seo::website(),
                Seo::webPage($title.' — '.config('app.name'), $description, '/pricing'),
            ]),
        ]);
    }

    public function about(): Response
    {
        return Seo::page('About', [], 'About', 'QuickSolve is a small set of practical tools and templates for people running a business without a large software stack. It does not publish fake testimonials.', '/about');
    }

    public function privacy(): Response
    {
        return Seo::page('Privacy', [], 'Privacy policy', 'How QuickSolve handles account data, Stripe payments, downloads, and usage records. Card numbers stay with Stripe.', '/privacy');
    }

    public function terms(): Response
    {
        return Seo::page('Terms', [], 'Terms of service', 'Terms for QuickSolve tools, monthly Stripe subscriptions, and digital downloads. A checkout redirect is not proof of payment.', '/terms');
    }
}
