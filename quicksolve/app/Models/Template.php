<?php

namespace App\Models;

use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'whats_included',
        'price',
        'currency',
        'preview_image',
        'private_file_path',
        'is_featured',
        'is_published',
    ];

    protected $hidden = [
        'private_file_path',
    ];

    protected function casts(): array
    {
        return [
            'whats_included' => 'array',
            'price' => 'integer',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function fileExists(): bool
    {
        return is_string($this->private_file_path)
            && $this->private_file_path !== ''
            && Storage::disk('local')->exists($this->private_file_path);
    }
}
