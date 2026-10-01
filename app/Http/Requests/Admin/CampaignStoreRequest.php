<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CampaignStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'offer_title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'product_id' => 'required|integer|exists:products,id',
            'video_url' => 'nullable|string|max:500',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'special_price' => 'nullable|numeric|min:0',
            'free_shipping' => 'nullable',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'image_one' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'image_two' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'image_three' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'image' => 'nullable|array',
            'image.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'description_title' => 'nullable|string',
            'review' => 'nullable|string|max:255',
            'status' => 'nullable',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }
}