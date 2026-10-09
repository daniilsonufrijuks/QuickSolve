<?php

namespace App\Models;

use App\Enums\AccessType;
use Database\Factories\ToolFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tool extends Model
{
    /** @use HasFactory<ToolFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'long_description',
        'icon',
        'access_type',
        'is_featured',
        'is_published',
        'popularity',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'access_type' => AccessType::class,
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'popularity' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
