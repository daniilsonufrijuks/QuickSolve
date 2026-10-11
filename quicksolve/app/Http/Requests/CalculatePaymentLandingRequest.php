<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use Illuminate\Foundation\Http\FormRequest;

class CalculatePaymentLandingRequest extends FormRequest
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
            'issue_date' => ['required', 'date_format:Y-m-d'],
            'terms' => ['required', 'in:net_7,net_14,net_30,net_45,net_60,eom,eom_plus_15'],
            'pay_run' => ['required', 'in:any,weekly,monthly_day,last_weekday'],
            'weekday' => ['required_if:pay_run,weekly', 'nullable', 'integer', 'min:1', 'max:5'],
            'month_day' => ['required_if:pay_run,monthly_day', 'nullable', 'integer', 'min:1', 'max:28'],
            'skip_weekends' => ['sometimes', 'boolean'],
            'settlement_days' => ['required', 'integer', 'min:0', 'max:14'],
            'holiday_dates' => ['nullable', 'array', 'max:24'],
            'holiday_dates.*' => ['date_format:Y-m-d'],
        ];
    }
}
