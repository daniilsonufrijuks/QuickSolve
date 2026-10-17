<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use App\Services\Documents\CvThemeCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportJsonResumeRequest extends FormRequest
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
            'template' => ['nullable', Rule::in(CvThemeCatalog::ids())],
            'resume' => ['required', 'array'],
            'resume.basics' => ['nullable', 'array'],
            'resume.work' => ['nullable', 'array', 'max:12'],
            'resume.education' => ['nullable', 'array', 'max:8'],
            'resume.skills' => ['nullable', 'array', 'max:30'],
        ];
    }
}
