<?php

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSettingRequest extends FormRequest
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
        $setting = $this->route('setting');
        $settingId = $setting instanceof Setting ? $setting->id : null;

        return [
            'key' => ['required', 'string', 'max:191', 'regex:/^[a-z0-9._-]+$/', Rule::unique('settings', 'key')->ignore($settingId)],
            'value' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
