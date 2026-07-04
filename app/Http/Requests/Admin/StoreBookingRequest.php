<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:32'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'total_amount' => ['required', 'numeric', 'min:1'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'booking_status' => ['nullable', 'in:pending,confirmed,cancelled,completed'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'اسم العميل',
            'customer_phone' => 'رقم الجوال',
            'start_date' => 'تاريخ الدخول',
            'end_date' => 'تاريخ الخروج',
            'total_amount' => 'المبلغ الإجمالي',
            'deposit_amount' => 'العربون',
            'booking_status' => 'حالة الحجز',
            'notes' => 'ملاحظات',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'string' => 'حقل :attribute يجب أن يكون نصاً.',
            'max' => 'حقل :attribute يجب ألا يتجاوز :max حرفاً.',
            'date' => 'حقل :attribute يجب أن يكون تاريخاً صحيحاً.',
            'after_or_equal' => 'حقل :attribute يجب أن يكون اليوم أو بعده.',
            'after' => 'حقل :attribute يجب أن يكون بعد :date.',
            'numeric' => 'حقل :attribute يجب أن يكون رقماً.',
            'min' => 'حقل :attribute يجب أن يكون :min على الأقل.',
            'in' => 'حقل :attribute غير صالح.',
        ];
    }
}