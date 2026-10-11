<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use App\Services\Documents\EmailTemplateComposer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmailTemplateRequest extends FormRequest
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
            'template' => ['required', Rule::in(EmailTemplateComposer::IDS)],
            'tone' => ['nullable', 'in:casual,formal'],
            'from_name' => ['required', 'string', 'max:160'],
            'from_role' => ['nullable', 'string', 'max:160'],
            'from_email' => ['nullable', 'email', 'max:160'],
            'to_name' => ['required', 'string', 'max:160'],
            'company' => ['nullable', 'string', 'max:160'],
            'topic' => ['nullable', 'string', 'max:200'],
            'amount' => ['nullable', 'string', 'max:40'],
            'due_date' => ['nullable', 'string', 'max:40'],
            'extra' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
