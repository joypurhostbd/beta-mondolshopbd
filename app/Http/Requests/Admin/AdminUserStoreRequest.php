<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:155',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|same:confirm-password',
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'status' => 'nullable',
        ];
    }
}