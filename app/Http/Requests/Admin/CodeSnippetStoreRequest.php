<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CodeSnippetStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'type' => 'required|in:html,javascript,css,text',
            'location' => 'required|in:head,body_open,footer',
            'code' => 'required|string',
            'status' => 'nullable',
            'priority' => 'nullable|integer|min:1|max:9999',
            'device_target' => 'required|in:all,desktop,mobile',
            'target_pages' => 'required|in:all,homepage,checkout,thank_you,custom',
            'custom_page_urls' => 'nullable|string|max:1000',
            'auth_condition' => 'required|in:all,logged_in,guest',
            'description' => 'nullable|string|max:500',
        ];
    }
}
