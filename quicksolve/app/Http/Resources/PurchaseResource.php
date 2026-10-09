<?php

namespace App\Http\Resources;

use App\Models\Purchase;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Purchase */
class PurchaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'formatted_amount' => Money::formatWithCurrency($this->amount, $this->currency),
            'status' => $this->status->value,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'template' => [
                'name' => $this->template?->name,
                'slug' => $this->template?->slug,
            ],
            'can_download' => $request->user()
                ? $request->user()->can('download', $this->resource)
                : false,
        ];
    }
}
