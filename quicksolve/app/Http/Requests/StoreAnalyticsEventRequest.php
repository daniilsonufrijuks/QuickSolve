<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnalyticsEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:tool_opened,calculation_completed,generator_used,template_viewed,checkout_started,purchase_completed'],
            'slug' => ['required', 'string', 'max:120'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
