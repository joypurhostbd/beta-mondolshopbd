<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SystemUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'protocol' => ['required', 'string', 'in:ssh,https'],
            'repository_url' => ['required', 'string', 'max:255'],
            'branch' => ['required', 'string', 'max:100'],
            'https_token' => ['nullable', 'string', 'max:500'],
            'auto_run_composer' => ['nullable'],
            'auto_run_migrations' => ['nullable'],
            'auto_run_optimize' => ['nullable'],
            'auto_run_queue_restart' => ['nullable'],
            'auto_run_npm_build' => ['nullable'],
            'auto_generate_key' => ['nullable'],
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'repository_url' => 'Git Repository URL',
            'branch' => 'Target Branch',
            'protocol' => 'Protocol',
        ];
    }
}
