<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure CAPI columns exist if table was created in older schema
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

        // 2. Configure default active pixel 2183884522084882
        if (!app()->environment('testing')) {
            $pixelId = '2183884522084882';
            $token = env('META_CAPI_ACCESS_TOKEN', null);
            $testCode = env('META_TEST_EVENT_CODE', null);

            $existing = DB::table('ecom_pixels')->where('code', $pixelId)->first();

            if ($existing) {
                DB::table('ecom_pixels')->where('id', $existing->id)->update([
                    'status' => 1,
                    'capi_status' => 1,
                    'access_token' => $existing->access_token ?: $token,
                    'test_event_code' => $existing->test_event_code ?: $testCode,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('ecom_pixels')->insert([
                    'code' => $pixelId,
                    'access_token' => $token,
                    'test_event_code' => $testCode,
                    'status' => 1,
                    'capi_status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep data intact on rollback
    }
};
