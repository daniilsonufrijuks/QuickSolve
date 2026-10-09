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
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Response;

class ToolController extends Controller
{
    public function index(Request $request, ToolCatalog $catalog): Response
    {
        $tools = $catalog->paginate($request->only(['q', 'category', 'access', 'sort']));

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
        ], 'Free and premium business tools', 'Search calculators, writing tools, and document builders for a small business.', '/tools');
    }

    public function show(Request $request, string $slug, UsageRecorder $usage, PlanResolver $plans): Response
    {
        $tool = Tool::query()->published()->with('category')->where('slug', $slug)->firstOrFail();
        $usage->record('tool_opened', $tool->slug, $request->user(), $request);

        $related = Tool::query()
            ->published()
            ->with('category')
            ->where('category_id', $tool->category_id)
            ->whereKeyNot($tool->id)
            ->orderByDesc('popularity')
            ->limit(3)
            ->get();

        $locked = $tool->access_type === AccessType::Premium && ! $plans->allowsPremiumTools($request->user());

        return Seo::page('tools/Show', [
            'tool' => (new ToolResource($tool))->resolve(),
            'related' => ToolResource::collection($related)->resolve(),
            'locked' => $locked,
            'usage' => $tool->slug === 'product-description-generator'
                ? $plans->generatorUsage($request->user(), $request)
                : null,
            'demoMode' => ! is_string(config('quicksolve.ai.api_key')) || config('quicksolve.ai.api_key') === '',
            'premium' => $plans->allowsPremiumTools($request->user()),
        ], $tool->name, $tool->description, '/tools/'.$tool->slug);
    }
}
