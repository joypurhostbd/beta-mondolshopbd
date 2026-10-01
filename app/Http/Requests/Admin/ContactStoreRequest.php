<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContactStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:50',
            'address' => 'required|string|max:500',
            'hotline' => 'nullable|string|max:50',
            'hotmail' => 'nullable|email|max:50',
            'maplink' => 'nullable|string',
            'status' => 'nullable',
        ];
    }
}