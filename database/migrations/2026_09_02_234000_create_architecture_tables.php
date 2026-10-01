<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. audit_logs
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_type', 100)->nullable();
                $table->string('action', 100)->index();
                $table->string('entity_type', 150)->index();
                $table->string('entity_id', 100)->index();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        // 2. outbox_messages
        if (!Schema::hasTable('outbox_messages')) {
            Schema::create('outbox_messages', function (Blueprint $table) {
                $table->id();
                $table->string('event_name', 150)->index();
                $table->json('payload');
                $table->string('status', 50)->default('pending')->index();
                $table->integer('retry_count')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. idempotency_keys
        if (!Schema::hasTable('idempotency_keys')) {
            Schema::create('idempotency_keys', function (Blueprint $table) {
                $table->id();
                $table->string('idempotency_key', 255)->unique();
                $table->string('request_path', 255);
                $table->string('request_hash', 64);
                $table->longText('response_body')->nullable();
                $table->integer('status_code')->default(200);
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('outbox_messages');
        Schema::dropIfExists('audit_logs');
    }
};