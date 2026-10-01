<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->tinyInteger('whatsapp_dynamic_context')->default(1)->after('whatsapp_message');
            $table->tinyInteger('whatsapp_product_button')->default(1)->after('whatsapp_dynamic_context');
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_dynamic_context', 'whatsapp_product_button']);
        });
    }
};