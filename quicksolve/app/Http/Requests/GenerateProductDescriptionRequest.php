<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateProductDescriptionRequest extends FormRequest
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
            'product_name' => ['required', 'string', 'max:120'],
            'product_category' => ['required', 'string', 'max:80'],
            'product_features' => ['required', 'string', 'max:2000'],
            'target_audience' => ['required', 'string', 'max:160'],
            'tone' => ['required', 'in:professional,friendly,playful,luxury,direct'],
            'length' => ['required', 'in:short,medium,long'],
            'format' => ['required', 'in:plain,bullets,listing'],
            'include_call_to_action' => ['sometimes', 'boolean'],
        ];
    }
}
