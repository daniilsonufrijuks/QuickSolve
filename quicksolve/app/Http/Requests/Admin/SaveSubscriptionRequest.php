<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Cashier\Subscription;

class SaveSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('stripe_id')) {
            $this->merge([
                'stripe_id' => 'sub_admin_'.Str::lower((string) Str::ulid()),
            ]);
        }

        if (! $this->filled('type')) {
            $this->merge(['type' => 'default']);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $subscription = $this->route('subscription');
        $subscriptionId = $subscription instanceof Subscription ? $subscription->id : null;

        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'string', 'max:32'],
            'stripe_id' => ['required', 'string', 'max:191', Rule::unique('subscriptions', 'stripe_id')->ignore($subscriptionId)],
            'stripe_status' => ['required', 'in:active,trialing,canceled,incomplete,incomplete_expired,past_due,unpaid,paused'],
            'stripe_price' => ['required', 'string', 'max:191'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'ends_at' => ['nullable', 'date'],
        ];
    }
}
