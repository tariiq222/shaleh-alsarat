<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank_transfer,other'],
            'payment_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'المبلغ',
            'payment_method' => 'طريقة الدفع',
            'payment_date' => 'تاريخ الدفع',
            'note' => 'ملاحظة',
            'receipt' => 'الإيصال',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'حقل :attribute مطلوب.',
            'numeric' => 'حقل :attribute يجب أن يكون رقماً.',
            'min' => 'حقل :attribute يجب أن يكون :min على الأقل.',
            'in' => 'حقل :attribute غير صالح.',
            'date' => 'حقل :attribute يجب أن يكون تاريخاً صحيحاً.',
            'string' => 'حقل :attribute يجب أن يكون نصاً.',
            'max' => 'حقل :attribute يجب ألا يتجاوز :max حرفاً.',
            'file' => 'حقل :attribute يجب أن يكون ملفاً.',
            'mimes' => 'حقل :attribute يجب أن يكون من نوع: :values.',
        ];
    }
}