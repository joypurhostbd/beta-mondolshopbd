<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'customer_id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'review' => 'required|string',
            'ratting' => 'required_without:rating|nullable|integer|min:1|max:5',
            'rating' => 'required_without:ratting|nullable|integer|min:1|max:5',
            'status' => 'nullable',
        ];
    }
}