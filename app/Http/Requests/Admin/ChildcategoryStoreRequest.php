<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ChildcategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'childcategoryName' => 'required',
            'subcategory_id' => 'required',
            'status' => 'required',
        ];
    }
}