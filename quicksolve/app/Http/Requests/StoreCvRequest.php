<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCvRequest extends FormRequest
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
            'template' => ['required', Rule::in([
                'classic',
                'modern',
                'compact',
                'executive',
                'timeline',
                'creative',
                'split',
                'banner',
                'mosaic',
                'atlantic',
                'portrait',
                'mono',
                'academic',
                'sand',
                'rose',
                'slate',
                'forest',
            ])],
            'full_name' => ['required', 'string', 'max:160'],
            'photo' => [
                'nullable',
                'string',
                'max:120000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || preg_match('/^data:image\/jpeg;base64,[A-Za-z0-9\/+=]+$/', $value) !== 1) {
                        $fail('The '.$attribute.' must be a JPEG image.');
                    }
                },
            ],
            'headline' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'location' => ['nullable', 'string', 'max:120'],
            'website' => ['nullable', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'skills' => ['nullable', 'array', 'max:30'],
            'skills.*' => ['required', 'string', 'max:80'],
            'experience' => ['nullable', 'array', 'max:12'],
            'experience.*.role' => ['required', 'string', 'max:160'],
            'experience.*.company' => ['nullable', 'string', 'max:160'],
            'experience.*.dates' => ['nullable', 'string', 'max:80'],
            'experience.*.details' => ['nullable', 'string', 'max:1500'],
            'education' => ['nullable', 'array', 'max:8'],
            'education.*.school' => ['required', 'string', 'max:160'],
            'education.*.credential' => ['nullable', 'string', 'max:160'],
            'education.*.dates' => ['nullable', 'string', 'max:80'],
        ];
    }
}
