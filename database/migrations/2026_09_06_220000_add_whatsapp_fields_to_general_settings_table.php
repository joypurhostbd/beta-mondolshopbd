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
            if (!Schema::hasColumn('general_settings', 'whatsapp_status')) {
                $table->tinyInteger('whatsapp_status')->default(1)->after('status');
            }
            if (!Schema::hasColumn('general_settings', 'whatsapp_number')) {
                $table->string('whatsapp_number', 50)->nullable()->after('whatsapp_status');
            }
            if (!Schema::hasColumn('general_settings', 'whatsapp_title')) {
                $table->string('whatsapp_title', 150)->nullable()->after('whatsapp_number');
            }
            if (!Schema::hasColumn('general_settings', 'whatsapp_message')) {
                $table->text('whatsapp_message')->nullable()->after('whatsapp_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'whatsapp_message')) {
                $table->dropColumn('whatsapp_message');
            }
            if (Schema::hasColumn('general_settings', 'whatsapp_title')) {
                $table->dropColumn('whatsapp_title');
            }
            if (Schema::hasColumn('general_settings', 'whatsapp_number')) {
                $table->dropColumn('whatsapp_number');
            }
            if (Schema::hasColumn('general_settings', 'whatsapp_status')) {
                $table->dropColumn('whatsapp_status');
            }
        });
    }
};