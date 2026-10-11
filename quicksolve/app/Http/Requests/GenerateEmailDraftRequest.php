<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use Illuminate\Foundation\Http\FormRequest;

class GenerateEmailDraftRequest extends FormRequest
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
            'topic' => ['required', 'string', 'max:200'],
            'situation' => ['required', 'in:sales,support,hiring,operations,partnership,billing,custom'],
            'tone' => ['required', 'in:casual,formal,direct'],
            'length' => ['required', 'in:short,medium'],
            'instruction' => ['required', 'string', 'max:2000'],
            'from_name' => ['required', 'string', 'max:160'],
            'to_name' => ['required', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'facts' => ['nullable', 'string', 'max:2000'],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1500'],
        ];
    }
}
