<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->string('order_btn_text', 100)->nullable()->default('অর্ডার করুন')->after('whatsapp_product_button');
            $table->string('order_btn_bg_color', 20)->nullable()->default('#fe5200')->after('order_btn_text');
            $table->string('order_btn_text_color', 20)->nullable()->default('#ffffff')->after('order_btn_bg_color');
            $table->string('order_btn_hover_bg_color', 20)->nullable()->default('#e04800')->after('order_btn_text_color');

            $table->string('cart_btn_text', 100)->nullable()->default('কার্টে যোগ')->after('order_btn_hover_bg_color');
            $table->string('cart_btn_bg_color', 20)->nullable()->default('#2f3543')->after('cart_btn_text');
            $table->string('cart_btn_text_color', 20)->nullable()->default('#ffffff')->after('cart_btn_bg_color');
            $table->string('cart_btn_hover_bg_color', 20)->nullable()->default('#1e222b')->after('cart_btn_text_color');

            $table->string('discount_badge_text', 50)->nullable()->default('ছাড়')->after('cart_btn_hover_bg_color');
            $table->string('discount_badge_bg_color', 20)->nullable()->default('#ffffff')->after('discount_badge_text');
            $table->string('discount_badge_text_color', 20)->nullable()->default('#fe5200')->after('discount_badge_bg_color');
            $table->string('discount_badge_border_color', 20)->nullable()->default('#fe5200')->after('discount_badge_text_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
