<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:64'],
            'platform' => ['required', 'in:whatsapp,instagram,twitter,snapchat,tiktok,telegram,facebook,youtube,other'],
            'url' => ['required', 'url', 'max:500'],
            'handle' => ['nullable', 'string', 'max:64'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم الحساب',
            'platform' => 'المنصة',
            'url' => 'الرابط',
            'handle' => 'اسم المستخدم',
            'sort_order' => 'الترتيب',
            'is_active' => 'مفعّل',
        ];
    }
}