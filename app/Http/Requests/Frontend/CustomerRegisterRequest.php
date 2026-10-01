<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:155',
            'phone' => 'required|string|max:55|unique:customers,phone',
            'password' => 'required|string|min:6',
        ];
    }
}