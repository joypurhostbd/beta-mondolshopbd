<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $id = $this->id ?? $this->hidden_id;

        if ($id) {
            $this->merge([
                'id' => (int) $id,
                'hidden_id' => (int) $id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'hidden_id' => 'required|integer|exists:order_statuses,id',
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'status' => 'nullable',
        ];
    }
}