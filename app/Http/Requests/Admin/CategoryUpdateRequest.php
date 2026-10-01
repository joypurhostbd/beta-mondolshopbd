<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'               => 'required|integer|exists:categories,id',
            'name'             => 'required|string|max:155',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'meta_title'       => 'nullable|string|max:191',
            'meta_description' => 'nullable|string',
            'status'           => 'nullable',
            'front_view'       => 'nullable',
            'parent_id'        => 'nullable|integer',
        ];
    }
}