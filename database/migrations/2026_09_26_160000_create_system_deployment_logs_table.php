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
        Schema::create('system_deployment_logs', function (Blueprint $table) {
            $table->id();
            $table->string('status', 50)->default('running'); // running, success, failed
            $table->string('commit_hash', 100)->nullable();
            $table->text('commit_message')->nullable();
            $table->string('author', 100)->nullable();
            $table->string('trigger_type', 50)->default('manual_ui'); // manual_ui, webhook, cli
            $table->integer('duration_seconds')->default(0);
            $table->longText('log_output')->nullable();
            $table->index(['status'], 'idx_deployment_status');
            $table->index(['created_at'], 'idx_deployment_created_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_deployment_logs');
    }
};
