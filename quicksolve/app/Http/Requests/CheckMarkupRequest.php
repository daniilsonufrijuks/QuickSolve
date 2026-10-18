<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckMarkupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $source = $this->input('source');

        if (is_string($source)) {
            $this->merge([
                'source' => str_replace("\0", '', $source),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'source' => ['required', 'string', 'max:80000'],
            'mode' => ['sometimes', 'in:document,fragment'],
            'profile' => ['sometimes', 'in:css3svg,css3,css21,svg'],
        ];
    }
}
