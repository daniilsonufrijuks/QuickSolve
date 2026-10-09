<?php

namespace App\Http\Resources;

use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Tool */
class ToolResource extends JsonResource
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
            'long_description' => $this->long_description,
            'icon' => $this->icon,
            'access_type' => $this->access_type->value,
            'is_featured' => $this->is_featured,
            'popularity' => $this->popularity,
            'metadata' => $this->metadata,
            'category' => [
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'url' => route('tools.show', $this->slug),
        ];
    }
}
