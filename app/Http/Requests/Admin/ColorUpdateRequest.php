<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ColorUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer',
            'hidden_id' => 'nullable|integer',
            'colorName' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'status' => 'nullable',
        ];
    }
}