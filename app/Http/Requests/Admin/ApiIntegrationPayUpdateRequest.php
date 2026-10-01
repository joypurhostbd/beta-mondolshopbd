<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApiIntegrationPayUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:payment_gateways,id',
            'type' => 'nullable|string|max:55',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:255',
            'app_key' => 'nullable|string|max:255',
            'app_secret' => 'nullable|string|max:255',
            'base_url' => 'nullable|string|max:255',
            'success_url' => 'nullable|string|max:255',
            'return_url' => 'nullable|string|max:255',
            'prefix' => 'nullable|string|max:50',
            'status' => 'nullable|in:0,1',
        ];
    }
}