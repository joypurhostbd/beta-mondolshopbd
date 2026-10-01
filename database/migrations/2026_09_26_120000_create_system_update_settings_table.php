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
        Schema::create('system_update_settings', function (Blueprint $table) {
            $table->id();
            $table->string('protocol', 20)->default('ssh'); // ssh or https
            $table->string('repository_url')->nullable();
            $table->string('branch', 100)->default('main');
            $table->string('ssh_private_key_path')->nullable();
            $table->text('ssh_public_key')->nullable();
            $table->text('https_token')->nullable();
            $table->boolean('auto_run_composer')->default(true);
            $table->boolean('auto_run_migrations')->default(true);
            $table->timestamp('last_deployed_at')->nullable();
            $table->string('last_deployed_commit', 100)->nullable();
            $table->integer('last_deployed_duration')->nullable(); // in seconds
            $table->string('last_deployment_status', 30)->nullable(); // success, failed
            $table->longText('last_deployment_log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_update_settings');
    }
};
