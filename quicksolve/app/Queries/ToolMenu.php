<?php

namespace App\Queries;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Tool;
use App\Support\ToolCategoryOrder;

class ToolMenu
{
    /**
     * @return list<array{slug: string, name: string, description: string, tools: list<array{name: string, slug: string, access_type: string}>}>
     */
    public function categories(): array
    {
        return Category::query()
            ->where('type', CategoryType::Tool)
            ->with(['tools' => function ($query): void {
                $query->published()
                    ->orderBy('name')
                    ->select(['id', 'category_id', 'name', 'slug', 'access_type']);
            }])
            ->get()
            ->sortBy(fn (Category $category): array => [ToolCategoryOrder::rank($category->slug), $category->name])
            ->map(function (Category $category): ?array {
                if ($category->tools->isEmpty()) {
                    return null;
                }

                return [
                    'slug' => $category->slug,
                    'name' => $category->name,
                    'description' => (string) $category->description,
                    'tools' => $category->tools->map(fn (Tool $tool): array => [
                        'name' => $tool->name,
                        'slug' => $tool->slug,
                        'access_type' => $tool->access_type->value,
                    ])->values()->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
