<?php

namespace App\Http\Controllers;

use App\Http\Resources\TemplateResource;
use App\Http\Resources\ToolResource;
use App\Models\Template;
use App\Models\Tool;
use App\Services\Billing\PlanResolver;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request, PlanResolver $plans): Response
    {
        $featuredTools = Tool::query()->published()->where('is_featured', true)->with('category')->orderByDesc('popularity')->limit(3)->get();
        $popularTools = Tool::query()->published()->with('category')->orderByDesc('popularity')->limit(6)->get();
        $templates = Template::query()->published()->with('category')->orderByDesc('is_featured')->orderBy('name')->limit(3)->get();
        $key = config('quicksolve.ai.api_key');
        $title = 'QuickSolve — Small tools. Smarter business.';
        $description = 'Free invoice PDFs, profit calculators, and honest writing help for small businesses. Five Pro-tool uses then a monthly plan. Templates download after Stripe payment.';
        $faqs = [
            ['q' => 'Do I need an account to use the calculators?', 'a' => 'No. The profit calculator and the invoice PDF work without an account. Saving invoices and buying templates requires a login.'],
            ['q' => 'When is a description a live DeepSeek response?', 'a' => 'Only when a DeepSeek API key is configured on the server and the request succeeds. Otherwise the page stays in demo mode and says so.'],
            ['q' => 'Does a checkout redirect activate Pro?', 'a' => 'No. Pro and Business stay inactive until Stripe confirms the subscription through a verified webhook.'],
        ];

        return Seo::page('Home', [
            'featuredTools' => ToolResource::collection($featuredTools)->resolve(),
            'popularTools' => ToolResource::collection($popularTools)->resolve(),
            'templates' => TemplateResource::collection($templates)->resolve(),
            'plans' => $this->publicPlans(),
            'demoMode' => ! is_string($key) || $key === '',
            'chatUsage' => $plans->generatorUsage($request->user(), $request),
        ], $title, $description, '/', [
            'branded' => true,
            'jsonLd' => Seo::graph(array_filter([
                Seo::organization(),
                Seo::website(),
                Seo::webPage($title, $description, '/', 'WebPage'),
                Seo::faqPage($faqs),
            ])),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function publicPlans(): array
    {
        return collect(config('quicksolve.plans'))
            ->map(fn (array $plan, string $key) => [
                'key' => $key,
                'name' => $plan['name'],
                'price' => $plan['monthly_price'],
                'formatted_price' => $plan['monthly_price'] === 0 ? '€0' : '€'.number_format($plan['monthly_price'] / 100, 0),
                'interval' => $plan['monthly_price'] === 0 ? null : 'month',
                'description' => $plan['description'],
                'features' => $plan['features'],
                'checkout' => in_array($key, ['pro', 'business'], true),
            ])
            ->values()
            ->all();
    }
}
