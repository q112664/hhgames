<?php

namespace App\Http\Requests\Resources;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResourceFileRequest extends FormRequest
{
    /**
     * Determine whether the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'in:Windows,安卓,模拟器'],
            'language' => ['required', 'in:简体中文,繁体中文,日语,英语'],
            'size' => ['required', 'string', 'max:40'],
            'code' => ['nullable', 'string', 'max:120'],
            'extract_code' => ['nullable', 'string', 'max:120'],
            'download_url' => ['nullable', 'url', 'max:2048'],
            'download_detail' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
