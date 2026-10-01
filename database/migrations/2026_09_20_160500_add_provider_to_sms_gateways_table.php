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
        if (Schema::hasTable('sms_gateways') && !Schema::hasColumn('sms_gateways', 'provider')) {
            Schema::table('sms_gateways', function (Blueprint $table) {
                $table->string('provider', 50)->default('joypurhost')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sms_gateways') && Schema::hasColumn('sms_gateways', 'provider')) {
            Schema::table('sms_gateways', function (Blueprint $table) {
                $table->dropColumn('provider');
            });
        }
    }
};
