<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannerUpdateRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:banners,id',
            'hidden_id' => 'nullable|integer|exists:banners,id',
            'category_id' => 'required|integer|exists:banner_categories,id',
            'link' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
            'status' => 'nullable',
        ];
    }
}