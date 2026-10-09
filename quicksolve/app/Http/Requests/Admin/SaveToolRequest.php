<?php

namespace App\Http\Requests\Admin;

use App\Enums\AccessType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveToolRequest extends FormRequest
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
        $toolId = $this->route('tool')?->id;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['required', 'string', 'max:160', 'alpha_dash', Rule::unique('tools', 'slug')->ignore($toolId)],
            'description' => ['required', 'string', 'max:1000'],
            'long_description' => ['nullable', 'string', 'max:10000'],
            'icon' => ['nullable', 'string', 'max:40'],
            'access_type' => ['required', Rule::enum(AccessType::class)],
            'is_featured' => ['sometimes', 'boolean'],
            'is_published' => ['sometimes', 'boolean'],
            'popularity' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
