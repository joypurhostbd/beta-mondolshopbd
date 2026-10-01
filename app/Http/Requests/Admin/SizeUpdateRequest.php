<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SizeUpdateRequest extends FormRequest
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
            'sizeName' => 'required|string|max:255',
            'status' => 'nullable',
        ];
    }
}