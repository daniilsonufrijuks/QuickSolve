<?php

namespace App\Http\Controllers;

use App\Enums\CategoryType;
use App\Enums\PurchaseStatus;
use App\Http\Resources\TemplateResource;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Template;
use App\Queries\TemplateCatalog;
use App\Services\Analytics\UsageRecorder;
use App\Support\Seo;
use Illuminate\Http\Request;
use Inertia\Response;

class TemplateController extends Controller
{
    public function index(Request $request, TemplateCatalog $catalog): Response
    {
        $templates = $catalog->paginate($request->only(['q', 'category']));

        return Seo::page('templates/Index', [
            'filters' => [
                'q' => $request->string('q')->toString(),
                'category' => $request->string('category')->toString(),
            ],
            'categories' => Category::query()->where('type', CategoryType::Template)->orderBy('name')->get(['id', 'name', 'slug', 'description']),
            'templates' => [
                'data' => TemplateResource::collection($templates->items())->resolve(),
                'meta' => [
                    'current_page' => $templates->currentPage(),
                    'last_page' => $templates->lastPage(),
                    'total' => $templates->total(),
                ],
            ],
        ], 'Business templates', 'Download practical spreadsheets and starter kits for finance, freelance work, and online selling.', '/templates');
    }

    public function show(Request $request, string $slug, UsageRecorder $usage): Response
    {
        $template = Template::query()->published()->with('category')->where('slug', $slug)->firstOrFail();
        $usage->record('template_viewed', $template->slug, $request->user(), $request);

        $owned = $request->user()
            ? Purchase::query()
                ->where('user_id', $request->user()->id)
                ->where('template_id', $template->id)
                ->where('status', PurchaseStatus::Paid)
                ->exists()
            : false;

        $related = Template::query()
            ->published()
            ->with('category')
            ->where('category_id', $template->category_id)
            ->whereKeyNot($template->id)
            ->limit(3)
            ->get();

        return Seo::page('templates/Show', [
            'template' => (new TemplateResource($template))->resolve(),
            'related' => TemplateResource::collection($related)->resolve(),
            'owned' => $owned,
        ], $template->name, $template->description, '/templates/'.$template->slug);
    }
}
