<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'white_logo',
        'dark_logo',
        'favicon',
        'copyright',
        'footer_about',
        'footer_phone',
        'footer_email',
        'footer_address',
        'footer_useful_links_title',
        'footer_info_links_title',
        'newsletter_title',
        'newsletter_text',
        'newsletter_status',
        'app_download_title',
        'app_download_status',
        'play_store_url',
        'app_store_url',
        'features_status',
        'feature1_title',
        'feature1_subtitle',
        'feature2_title',
        'feature2_subtitle',
        'feature3_title',
        'feature3_subtitle',
        'feature4_title',
        'feature4_subtitle',
        'show_payment_methods',
        'payment_methods_image',
        'status',
        'whatsapp_status',
        'whatsapp_number',
        'whatsapp_title',
        'whatsapp_message',
        'whatsapp_dynamic_context',
        'whatsapp_product_button',
        'order_btn_text',
        'order_btn_bg_color',
        'order_btn_text_color',
        'order_btn_hover_bg_color',
        'cart_btn_text',
        'cart_btn_bg_color',
        'cart_btn_text_color',
        'cart_btn_hover_bg_color',
        'discount_badge_text',
        'discount_badge_bg_color',
        'discount_badge_text_color',
        'discount_badge_border_color',
    ];

    protected $casts = [
        'status' => 'integer',
        'newsletter_status' => 'integer',
        'app_download_status' => 'integer',
        'features_status' => 'integer',
        'show_payment_methods' => 'integer',
        'whatsapp_status' => 'integer',
        'whatsapp_dynamic_context' => 'integer',
        'whatsapp_product_button' => 'integer',
    ];
}
