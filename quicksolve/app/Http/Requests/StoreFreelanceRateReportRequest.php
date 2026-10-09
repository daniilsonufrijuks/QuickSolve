<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use Illuminate\Foundation\Http\FormRequest;

class StoreFreelanceRateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(PlanResolver::class)->allowsPremiumTools($this->user());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'income_target' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'annual_expenses' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'hours_per_day' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'days_per_week' => ['required', 'integer', 'min:1', 'max:7'],
            'weeks_per_year' => ['required', 'integer', 'min:1', 'max:52'],
            'unpaid_leave_days' => ['nullable', 'integer', 'min:0', 'max:366'],
            'currency' => ['required', 'in:EUR,USD,GBP'],
        ];
    }
}
