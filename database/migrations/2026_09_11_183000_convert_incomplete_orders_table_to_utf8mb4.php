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
        if (Schema::hasTable('incomplete_orders') && DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `incomplete_orders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incomplete_orders') && DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `incomplete_orders` CONVERT TO CHARACTER SET latin1 COLLATE latin1_swedish_ci');
        }
    }
};
