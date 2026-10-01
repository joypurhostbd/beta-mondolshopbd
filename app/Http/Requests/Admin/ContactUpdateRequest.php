<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContactUpdateRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:contacts,id',
            'hidden_id' => 'nullable|integer|exists:contacts,id',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:50',
            'address' => 'required|string|max:500',
            'hotline' => 'nullable|string|max:50',
            'hotmail' => 'nullable|email|max:50',
            'maplink' => 'nullable|string',
            'status' => 'nullable',
        ];
    }
}