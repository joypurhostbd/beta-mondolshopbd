<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Setting\Application\DTOs\TagManagerDTO;

class TagManagerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $code = strtoupper(trim((string) $this->code));
            if (preg_match('/(GTM-[A-Z0-9_-]+)/i', $code, $matches)) {
                $code = strtoupper($matches[1]);
            } elseif (!empty($code) && !str_starts_with($code, 'GTM-') && preg_match('/^[A-Z0-9_-]+$/i', $code)) {
                $code = 'GTM-' . $code;
            }
            $this->merge(['code' => $code]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^GTM-[A-Z0-9_-]{3,30}$/i',
                'unique:google_tag_managers,code',
            ],
            'status' => 'nullable|in:0,1',
            'is_server_side' => 'nullable|in:0,1',
            'server_container_url' => 'nullable|url|max:255',
            'measurement_id' => 'nullable|string|max:50',
            'api_secret' => 'nullable|string|max:100',
            'custom_loader_domain' => 'nullable|in:0,1',
            'description' => 'nullable|string|max:1000',
            'events' => 'nullable|array',
            'events.*.is_web_enabled' => 'nullable|in:0,1',
            'events.*.is_server_enabled' => 'nullable|in:0,1',
            'events.*.custom_event_name' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'The Google Tag Manager ID format is invalid. It should start with GTM- followed by alphanumeric characters (e.g. GTM-K89745P).',
            'code.unique' => 'A Google Tag Manager container with this ID is already registered.',
            'server_container_url.url' => 'The Server Container URL must be a valid URL (e.g. https://gtm.yourdomain.com).',
        ];
    }

    public function toDTO(): TagManagerDTO
    {
        return TagManagerDTO::fromArray($this->validated());
    }
}