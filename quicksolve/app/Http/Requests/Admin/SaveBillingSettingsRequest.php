<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveBillingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pro_price_id' => ['nullable', 'string', 'max:80', 'regex:/^price_[A-Za-z0-9]+$/'],
            'business_price_id' => ['nullable', 'string', 'max:80', 'regex:/^price_[A-Za-z0-9]+$/'],
        ];
    }
}
