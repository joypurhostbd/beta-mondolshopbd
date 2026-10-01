<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer',
            'hidden_id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'white_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'dark_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'copyright' => 'nullable|string|max:255',
            'footer_about' => 'nullable|string|max:1000',
            'footer_phone' => 'nullable|string|max:50',
            'footer_email' => 'nullable|string|max:100',
            'footer_address' => 'nullable|string|max:255',
            'footer_useful_links_title' => 'nullable|string|max:100',
            'footer_info_links_title' => 'nullable|string|max:100',
            'newsletter_title' => 'nullable|string|max:100',
            'newsletter_text' => 'nullable|string|max:500',
            'newsletter_status' => 'nullable',
            'app_download_title' => 'nullable|string|max:100',
            'app_download_status' => 'nullable',
            'play_store_url' => 'nullable|string|max:255',
            'app_store_url' => 'nullable|string|max:255',
            'features_status' => 'nullable',
            'feature1_title' => 'nullable|string|max:100',
            'feature1_subtitle' => 'nullable|string|max:150',
            'feature2_title' => 'nullable|string|max:100',
            'feature2_subtitle' => 'nullable|string|max:150',
            'feature3_title' => 'nullable|string|max:100',
            'feature3_subtitle' => 'nullable|string|max:150',
            'feature4_title' => 'nullable|string|max:100',
            'feature4_subtitle' => 'nullable|string|max:150',
            'show_payment_methods' => 'nullable',
            'payment_methods_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'status' => 'nullable',
            'whatsapp_status' => 'nullable',
            'whatsapp_number' => 'nullable|string|max:50',
            'whatsapp_title' => 'nullable|string|max:150',
            'whatsapp_message' => 'nullable|string|max:500',
            'whatsapp_dynamic_context' => 'nullable',
            'whatsapp_product_button' => 'nullable',
            'order_btn_text' => 'nullable|string|max:100',
            'order_btn_bg_color' => 'nullable|string|max:20',
            'order_btn_text_color' => 'nullable|string|max:20',
            'order_btn_hover_bg_color' => 'nullable|string|max:20',
            'cart_btn_text' => 'nullable|string|max:100',
            'cart_btn_bg_color' => 'nullable|string|max:20',
            'cart_btn_text_color' => 'nullable|string|max:20',
            'cart_btn_hover_bg_color' => 'nullable|string|max:20',
            'discount_badge_text' => 'nullable|string|max:50',
            'discount_badge_bg_color' => 'nullable|string|max:20',
            'discount_badge_text_color' => 'nullable|string|max:20',
            'discount_badge_border_color' => 'nullable|string|max:20',
        ];
    }
}