<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeNewsletterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:191',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'দয়া করে আপনার ইমেইল প্রদান করুন।',
            'email.email'    => 'দয়া করে একটি সঠিক ইমেইল ঠিকানা প্রদান করুন।',
            'email.max'      => 'ইমেইল ঠিকানাটি সর্বোচ্চ ১৯১ অক্ষরের মধ্যে হতে হবে।',
        ];
    }
}
