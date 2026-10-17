<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use App\Services\Documents\CvThemeCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FetchJsonResumeRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:39', 'regex:/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?$/'],
            'template' => ['nullable', Rule::in(CvThemeCatalog::ids())],
        ];
    }
}
