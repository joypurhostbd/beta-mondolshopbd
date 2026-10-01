<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:155|unique:roles,name',
            'permission' => 'required|array',
            'permission.*' => 'integer|exists:permissions,id',
        ];
    }
}