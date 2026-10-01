<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SocialMediaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->has('id') && !$this->has('hidden_id')) {
            $this->merge(['hidden_id' => $this->id]);
        } elseif ($this->has('hidden_id') && !$this->has('id')) {
            $this->merge(['id' => $this->hidden_id]);
        }
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|exists:social_media,id',
            'hidden_id' => 'nullable|integer|exists:social_media,id',
            'title' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'link' => 'required|string|max:500',
            'color' => 'nullable|string|max:50',
            'status' => 'nullable',
        ];
    }
}