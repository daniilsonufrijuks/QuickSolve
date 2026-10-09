<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CategoryType;
use App\Http\Controllers\Controller;
use App\Http\Resources\TemplateResource;
use App\Http\Resources\ToolResource;
use App\Models\Category;
use App\Models\Template;
use App\Models\Tool;
use App\Queries\TemplateCatalog;
use App\Queries\ToolCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    public function tools(Request $request, ToolCatalog $catalog): AnonymousResourceCollection
    {
        return ToolResource::collection($catalog->paginate($request->only(['q', 'category', 'access', 'sort'])));
    }

    public function tool(string $slug): ToolResource
    {
        $tool = Tool::query()->published()->with('category')->where('slug', $slug)->firstOrFail();

        return new ToolResource($tool);
    }

    public function categories(Request $request): JsonResponse
    {
        $type = $request->string('type')->toString();
        $query = Category::query()->orderBy('name');

        if (in_array($type, ['tool', 'template'], true)) {
            $query->where('type', $type === 'tool' ? CategoryType::Tool : CategoryType::Template);
        }

        return response()->json([
            'data' => $query->get(['id', 'name', 'slug', 'type', 'description']),
        ]);
    }

    public function templates(Request $request, TemplateCatalog $catalog): AnonymousResourceCollection
    {
        return TemplateResource::collection($catalog->paginate($request->only(['q', 'category'])));
    }

    public function template(string $slug): TemplateResource
    {
        $template = Template::query()->published()->with('category')->where('slug', $slug)->firstOrFail();

        return new TemplateResource($template);
    }
}
