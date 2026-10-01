<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ColorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'colorName' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'status' => 'nullable',
        ];
    }
}