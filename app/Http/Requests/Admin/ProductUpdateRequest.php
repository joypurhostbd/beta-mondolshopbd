<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required',
            'new_price' => 'required',
            'purchase_price' => 'required',
            'stock' => 'required',
            'description' => 'required',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'featured_image_id' => 'nullable|integer|exists:productimages,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ];
    }
}