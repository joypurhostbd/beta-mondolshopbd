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
        Schema::table('ecom_pixels', function (Blueprint $table) {
            if (!Schema::hasColumn('ecom_pixels', 'access_token')) {
                $table->text('access_token')->nullable()->after('code');
            }
            if (!Schema::hasColumn('ecom_pixels', 'test_event_code')) {
                $table->string('test_event_code', 100)->nullable()->after('access_token');
            }
            if (!Schema::hasColumn('ecom_pixels', 'capi_status')) {
                $table->tinyInteger('capi_status')->default(1)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecom_pixels', function (Blueprint $table) {
            if (Schema::hasColumn('ecom_pixels', 'capi_status')) {
                $table->dropColumn('capi_status');
            }
            if (Schema::hasColumn('ecom_pixels', 'test_event_code')) {
                $table->dropColumn('test_event_code');
            }
            if (Schema::hasColumn('ecom_pixels', 'access_token')) {
                $table->dropColumn('access_token');
            }
        });
    }
};