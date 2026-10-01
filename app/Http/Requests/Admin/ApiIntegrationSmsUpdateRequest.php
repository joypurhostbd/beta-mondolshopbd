<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ApiIntegrationSmsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|exists:sms_gateways,id',
            'provider' => 'nullable|string|in:joypurhost,custom',
            'type' => 'nullable|string|max:100',
            'api_key' => 'nullable|string|max:255',
            'serderid' => 'nullable|string|max:155',
            'sender_id' => 'nullable|string|max:155',
            'url' => 'nullable|string|max:255',
            'status' => 'nullable|in:0,1',
            'order' => 'nullable|in:0,1',
            'forget_pass' => 'nullable|in:0,1',
            'password_g' => 'nullable|in:0,1',
            'admin_phone' => 'nullable|string|max:255',
        ];
    }
}