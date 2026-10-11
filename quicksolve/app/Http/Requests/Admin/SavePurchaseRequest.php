<?php

namespace App\Http\Requests\Admin;

use App\Enums\PurchaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePurchaseRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'template_id' => ['required', 'integer', 'exists:templates,id'],
            'amount' => ['required', 'integer', 'min:0', 'max:10000000'],
            'currency' => ['required', 'in:EUR,USD,GBP'],
            'status' => ['required', Rule::enum(PurchaseStatus::class)],
            'payment_reference' => ['nullable', 'string', 'max:191'],
        ];
    }
}
