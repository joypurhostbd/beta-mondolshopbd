<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewUpdateRequest extends FormRequest
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
            'product_id' => 'required|integer|exists:products,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'review' => 'required|string',
            'ratting' => 'required_without:rating|nullable|integer|min:1|max:5',
            'rating' => 'required_without:ratting|nullable|integer|min:1|max:5',
            'status' => 'nullable',
        ];
    }
}