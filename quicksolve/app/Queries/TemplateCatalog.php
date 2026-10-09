<?php

namespace App\Queries;

use App\Enums\CategoryType;
use App\Models\Template;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TemplateCatalog
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Template::query()->published()->with('category');
        $search = is_string($filters['q'] ?? null) ? trim($filters['q']) : '';

        if ($search !== '') {
            $search = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (is_string($filters['category'] ?? null) && $filters['category'] !== '') {
            $query->whereHas('category', function (Builder $builder) use ($filters) {
                $builder->where('slug', $filters['category'])->where('type', CategoryType::Template);
            });
        }

        return $query->orderByDesc('is_featured')->orderBy('name')->paginate(12)->withQueryString();
    }
}
