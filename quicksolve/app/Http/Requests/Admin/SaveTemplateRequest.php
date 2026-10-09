<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $templateId = $this->route('template')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', Rule::unique('templates', 'slug')->ignore($templateId)],
            'description' => ['required', 'string', 'max:10000'],
            'whats_included' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
            'currency' => ['required', 'in:EUR,USD,GBP'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'file' => [$this->isMethod('post') ? 'required' : 'nullable', 'file', 'mimes:csv,txt,pdf,zip,xlsx', 'max:10240'],
            'preview' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
