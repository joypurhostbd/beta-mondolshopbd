<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->hidden_id ?? $this->id;

        return [
            'hidden_id' => 'required|integer|exists:roles,id',
            'name' => 'required|string|max:155|unique:roles,name,' . $roleId,
            'permission' => 'nullable|array',
            'permission.*' => 'integer|exists:permissions,id',
        ];
    }
}