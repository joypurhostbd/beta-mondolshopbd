<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApiIntegrationCourierUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:courierapis,id',
            'type' => 'nullable|string|max:55',
            'api_key' => 'nullable|string|max:255',
            'client_id' => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:500',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:500',
            'grant_type' => 'nullable|string|max:100',
            'secret_key' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:255',
            'token' => 'nullable|string|max:500',
            'refresh_token' => 'nullable|string|max:1000',
            'token_expires_at' => 'nullable|date',
            'store_id' => 'nullable|integer',
            'status' => 'nullable|in:0,1',
        ];
    }
}
