<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use Illuminate\Foundation\Http\FormRequest;

class StoreScopeChangeRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->input('extra_hours') === '') {
            $merge['extra_hours'] = null;
        }

        if ($this->input('new_deadline') === '') {
            $merge['new_deadline'] = null;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

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
            'from_name' => ['required', 'string', 'max:160'],
            'from_email' => ['nullable', 'email', 'max:160'],
            'client_name' => ['required', 'string', 'max:160'],
            'project_name' => ['required', 'string', 'max:160'],
            'agreed_work' => ['required', 'string', 'max:2000'],
            'extra_request' => ['required', 'string', 'max:2000'],
            'extra_hours' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'extra_amount' => ['required', 'string', 'regex:/^\d+(\.\d{1,2})?$/'],
            'currency' => ['required', 'in:EUR,USD,GBP'],
            'new_deadline' => ['nullable', 'date_format:Y-m-d'],
            'hold_until_confirmed' => ['sometimes', 'boolean'],
        ];
    }
}
