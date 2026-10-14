<?php

namespace App\Http\Requests;

use App\Services\Billing\PlanResolver;
use Illuminate\Foundation\Http\FormRequest;

class GenerateCvChatRequest extends FormRequest
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
            'instruction' => ['required', 'string', 'max:2000'],
            'cv' => ['required', 'array'],
            'cv.full_name' => ['nullable', 'string', 'max:160'],
            'cv.headline' => ['nullable', 'string', 'max:160'],
            'cv.email' => ['nullable', 'string', 'max:160'],
            'cv.phone' => ['nullable', 'string', 'max:60'],
            'cv.location' => ['nullable', 'string', 'max:120'],
            'cv.website' => ['nullable', 'string', 'max:180'],
            'cv.summary' => ['nullable', 'string', 'max:2000'],
            'cv.skills' => ['nullable', 'array', 'max:30'],
            'cv.skills.*' => ['required', 'string', 'max:80'],
            'cv.experience' => ['nullable', 'array', 'max:12'],
            'cv.experience.*.role' => ['required', 'string', 'max:160'],
            'cv.experience.*.company' => ['nullable', 'string', 'max:160'],
            'cv.experience.*.dates' => ['nullable', 'string', 'max:80'],
            'cv.experience.*.details' => ['nullable', 'string', 'max:1500'],
            'cv.education' => ['nullable', 'array', 'max:8'],
            'cv.education.*.school' => ['required', 'string', 'max:160'],
            'cv.education.*.credential' => ['nullable', 'string', 'max:160'],
            'cv.education.*.dates' => ['nullable', 'string', 'max:80'],
            'history' => ['nullable', 'array', 'max:8'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string', 'max:1500'],
        ];
    }
}
