<?php

namespace App\Http\Controllers;

use App\Enums\AccessType;
use App\Enums\CategoryType;
use App\Http\Resources\ToolResource;
use App\Models\Category;
use App\Models\Tool;
use App\Queries\ToolCatalog;
use App\Services\Analytics\UsageRecorder;
use App\Services\Billing\PlanResolver;
use App\Services\Billing\SubscriptionSyncer;
use App\Services\Documents\CvThemeCatalog;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Response;

class ToolController extends Controller
{
    public function index(Request $request, ToolCatalog $catalog): Response
    {
        $tools = $catalog->paginate($request->only(['q', 'category', 'access', 'sort']));

        $searching = $request->filled('q');

        return Seo::page('tools/Index', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
                'access' => $request->string('access')->toString(),
                'sort' => $request->string('sort')->toString() ?: 'popularity',
            ],
            'categories' => Category::query()->where('type', CategoryType::Tool)->orderBy('name')->get(['id', 'name', 'slug', 'description']),
            'tools' => [
                'data' => ToolResource::collection($tools->items())->resolve(),
                'meta' => [
                    'current_page' => $tools->currentPage(),
                    'last_page' => $tools->lastPage(),
                    'total' => $tools->total(),
                ],
            ],
        ], 'Free and Pro business tools', 'Browse calculators, invoices, writing tools, and document builders. Guests can start without an account.', '/tools', [
            'robots' => $searching ? 'noindex,follow' : 'index,follow',
        ]);
    }

    public function show(Request $request, string $slug, UsageRecorder $usage, PlanResolver $plans, SubscriptionSyncer $syncer): Response
    {
        $tool = Tool::query()->published()->with('category')->where('slug', $slug)->firstOrFail();
        $user = $request->user();

        if ($user) {
            $syncer->refreshPaidAccess($user, $plans);
        }

        $usage->record('tool_opened', $tool->slug, $request->user(), $request);

        $related = Tool::query()
            ->published()
            ->with('category')
            ->where('category_id', $tool->category_id)
            ->whereKeyNot($tool->id)
            ->orderByDesc('popularity')
            ->limit(3)
            ->get();

        $subscribed = $plans->allowsLongDescriptions($request->user());
        $trial = $plans->premiumTrialUsage($request->user(), $request);
        $premium = $subscribed || $trial['remaining'] > 0;
        $locked = $tool->access_type === AccessType::Premium && ! $premium;

        if ($tool->access_type === AccessType::Premium && ! $locked && ! $subscribed) {
            $plans->consumePremiumTrial($request->user(), $request, $tool->slug);
            $trial = $plans->premiumTrialUsage($request->user(), $request);
        }

        $path = '/tools/'.$tool->slug;
        $description = Seo::describe($tool->long_description ?: $tool->description);
        $faqs = is_array($tool->metadata['faqs'] ?? null) ? $tool->metadata['faqs'] : [];
        $title = $tool->name;

        return Seo::page('tools/Show', [
            'tool' => (new ToolResource($tool))->resolve(),
            'related' => ToolResource::collection($related)->resolve(),
            'locked' => $locked,
            'usage' => in_array($tool->slug, ['product-description-generator', 'email-templates', 'cv-builder'], true)
                ? $plans->generatorUsage($request->user(), $request)
                : null,
            'trial' => $trial,
            'demoMode' => ! is_string(config('quicksolve.ai.api_key')) || config('quicksolve.ai.api_key') === '',
            'cvThemes' => $tool->slug === 'cv-builder' ? CvThemeCatalog::themed() : null,
            'premium' => $premium,
            'plan' => $plans->key($request->user()),
            'business' => $plans->isBusiness($request->user()),
        ], $title, $description, $path, [
            'jsonLd' => Seo::graph(array_filter([
                Seo::organization(),
                Seo::website(),
                Seo::webPage($title.' — '.config('app.name'), $description, $path),
                Seo::faqPage($faqs),
            ])),
        ]);
    }
}
