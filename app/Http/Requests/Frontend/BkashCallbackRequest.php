<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class BkashCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paymentID' => 'nullable|string',
            'status' => 'nullable|string',
            'signature' => 'nullable|string',
        ];
    }
}