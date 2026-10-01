<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PermissionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permId = $this->hidden_id ?? $this->id;

        return [
            'hidden_id' => 'required|integer|exists:permissions,id',
            'name' => 'required|string|max:155|unique:permissions,name,' . $permId,
            'guard_name' => 'nullable|string|max:50',
            'status' => 'nullable',
        ];
    }
}