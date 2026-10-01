<?php

declare(strict_types=1);

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
        Schema::table('system_update_settings', function (Blueprint $table) {
            $table->boolean('auto_run_optimize')->default(true)->after('auto_run_migrations');
            $table->boolean('auto_run_queue_restart')->default(true)->after('auto_run_optimize');
            $table->boolean('auto_run_npm_build')->default(false)->after('auto_run_queue_restart');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_update_settings', function (Blueprint $table) {
            $table->dropColumn(['auto_run_optimize', 'auto_run_queue_restart', 'auto_run_npm_build']);
        });
    }
};
