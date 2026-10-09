<?php

namespace App\Http\Resources;

use App\Models\Template;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Template */
class TemplateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'whats_included' => $this->whats_included ?? [],
            'price' => $this->price,
            'currency' => $this->currency,
            'formatted_price' => Money::formatWithCurrency($this->price, $this->currency),
            'preview_image' => $this->preview_image,
            'is_featured' => $this->is_featured,
            'purchasable' => $this->fileExists(),
            'category' => [
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'url' => route('templates.show', $this->slug),
        ];
    }
}
