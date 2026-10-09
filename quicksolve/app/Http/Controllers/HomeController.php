<?php

namespace App\Http\Controllers;

use App\Http\Resources\TemplateResource;
use App\Http\Resources\ToolResource;
use App\Models\Template;
use App\Models\Tool;
use App\Support\Seo;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featuredTools = Tool::query()->published()->where('is_featured', true)->with('category')->orderByDesc('popularity')->limit(3)->get();
        $popularTools = Tool::query()->published()->with('category')->orderByDesc('popularity')->limit(6)->get();
        $templates = Template::query()->published()->with('category')->orderByDesc('is_featured')->orderBy('name')->limit(3)->get();

        return Seo::page('Home', [
            'featuredTools' => ToolResource::collection($featuredTools)->resolve(),
            'popularTools' => ToolResource::collection($popularTools)->resolve(),
            'templates' => TemplateResource::collection($templates)->resolve(),
            'plans' => $this->publicPlans(),
        ], 'Small tools for everyday business work', 'Calculate profit, write product copy, build invoices, and download practical business templates.', '/');
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
