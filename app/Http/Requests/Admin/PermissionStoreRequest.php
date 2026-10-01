<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PermissionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:155|unique:permissions,name',
            'guard_name' => 'nullable|string|max:50',
            'status' => 'nullable',
        ];
    }
}