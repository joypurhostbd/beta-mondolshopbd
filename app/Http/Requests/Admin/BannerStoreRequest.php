<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:banner_categories,id',
            'link' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
            'status' => 'nullable',
        ];
    }
}