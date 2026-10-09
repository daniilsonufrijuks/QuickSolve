<?php

namespace App\Http\Resources;

use App\Models\GeneratedDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin GeneratedDocument */
class GeneratedDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type->value,
            'title' => $this->title,
            'structured_data' => $this->structured_data,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
