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
        if (Schema::hasTable('sms_gateways') && !Schema::hasColumn('sms_gateways', 'admin_phone')) {
            Schema::table('sms_gateways', function (Blueprint $table) {
                $table->string('admin_phone', 255)->nullable()->after('password_g');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sms_gateways') && Schema::hasColumn('sms_gateways', 'admin_phone')) {
            Schema::table('sms_gateways', function (Blueprint $table) {
                $table->dropColumn('admin_phone');
            });
        }
    }
};
