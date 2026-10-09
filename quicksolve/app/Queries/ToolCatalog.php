<?php

namespace App\Queries;

use App\Enums\CategoryType;
use App\Models\Tool;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ToolCatalog
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->query($filters)
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(array $filters): Builder
    {
        $query = Tool::query()->published()->with('category');
        $search = $this->searchTerm($filters['q'] ?? null);

        if ($search !== null) {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (is_string($filters['category'] ?? null) && $filters['category'] !== '') {
            $query->whereHas('category', function (Builder $builder) use ($filters) {
                $builder->where('slug', $filters['category'])->where('type', CategoryType::Tool);
            });
        }

        if (in_array($filters['access'] ?? null, ['free', 'premium'], true)) {
            $query->where('access_type', $filters['access']);
        }

        if (($filters['sort'] ?? 'popularity') === 'name') {
            $query->orderBy('name');
        } else {
            $query->orderByDesc('popularity')->orderBy('name');
        }

        return $query;
    }

    private function searchTerm(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return str_replace(['%', '_'], ['\%', '\_'], $value);
    }
}
