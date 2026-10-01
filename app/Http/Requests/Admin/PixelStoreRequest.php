<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PixelStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255',
            'access_token' => 'nullable|string',
            'test_event_code' => 'nullable|string|max:100',
            'status' => 'nullable',
            'capi_status' => 'nullable',
        ];
    }
}