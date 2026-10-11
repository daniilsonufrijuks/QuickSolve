<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plans = app(PlanResolver::class);

        if ($plans->allowsLongDescriptions($this->user())) {
            return true;
        }

        $trial = $plans->premiumTrialUsage($this->user(), $this);

        return $trial['remaining'] > 0 || ($trial['used'] > 0 && $trial['used'] <= $trial['limit']);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:160'],
            'business_email' => ['nullable', 'email', 'max:160'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_email' => ['nullable', 'email', 'max:160'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'quote_number' => ['required', 'string', 'max:40'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:issue_date'],
            'currency' => ['required', 'in:EUR,USD,GBP'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_instructions' => ['nullable', 'string', 'max:2000'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'custom_footer' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ];
    }
}
