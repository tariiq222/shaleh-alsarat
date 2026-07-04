<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'features' => ['nullable', 'string', 'max:2000'],
            'location_text' => ['nullable', 'string', 'max:500'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'whatsapp_number' => ['nullable', 'string', 'max:32'],
            'weekday_price' => ['required', 'numeric', 'min:0'],
            'weekend_price' => ['required', 'numeric', 'min:0'],
            'check_in_time' => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'check_out_time' => ['required', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم الشاليه',
            'description' => 'الوصف',
            'features' => 'المميزات',
            'location_text' => 'الموقع',
            'map_url' => 'رابط الخريطة',
            'whatsapp_number' => 'رقم الواتساب',
            'weekday_price' => 'سعر أيام الأسبوع',
            'weekend_price' => 'سعر نهاية الأسبوع',
            'check_in_time' => 'وقت الدخول',
            'check_out_time' => 'وقت الخروج',
            'is_active' => 'الحالة',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'string' => 'حقل :attribute يجب أن يكون نصاً.',
            'max' => 'حقل :attribute يجب ألا يتجاوز :max حرفاً.',
            'url' => 'حقل :attribute يجب أن يكون رابطاً صحيحاً.',
            'numeric' => 'حقل :attribute يجب أن يكون رقماً.',
            'min' => 'حقل :attribute يجب أن يكون :min على الأقل.',
            'regex' => 'حقل :attribute يجب أن يكون بصيغة HH:MM.',
            'boolean' => 'حقل :attribute يجب أن يكون true أو false.',
        ];
    }
}