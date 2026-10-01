<?php

namespace App\Http\Requests\Frontend;

use App\ValueObjects\Phone;
use Illuminate\Foundation\Http\FormRequest;

class CustomerOrderSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => Phone::normalize($this->input('phone')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:155'],
            'phone' => ['required', 'string', 'regex:/^01[3-9]\d{8}$/'],
            'address' => ['required', 'string'],
            'area' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'আপনার নাম লিখুন।',
            'phone.required' => 'আপনার মোবাইল নম্বর লিখুন।',
            'phone.regex' => 'সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 017XXXXXXXX)।',
            'address.required' => 'আপনার সম্পূর্ণ ঠিকানা লিখুন।',
            'area.required' => 'ডেলিভারি এরিয়া নির্বাচন করুন।',
        ];
    }
}