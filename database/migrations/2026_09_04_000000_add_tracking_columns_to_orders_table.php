<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'consignment_id')) {
                $table->string('consignment_id', 100)->nullable()->after('order_status');
            }
            if (!Schema::hasColumn('orders', 'tracking_code')) {
                $table->string('tracking_code', 100)->nullable()->after('consignment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'consignment_id')) {
                $table->dropColumn('consignment_id');
            }
            if (Schema::hasColumn('orders', 'tracking_code')) {
                $table->dropColumn('tracking_code');
            }
        });
    }
};
