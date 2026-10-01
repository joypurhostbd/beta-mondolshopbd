<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        // Ensure google_tag_managers engine is InnoDB for relational integrity
        try {
            DB::statement('ALTER TABLE google_tag_managers ENGINE = InnoDB');
        } catch (\Throwable $e) {}

        if (!Schema::hasTable('tag_manager_event_configs')) {
            Schema::create('tag_manager_event_configs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('tag_manager_id');
                $table->string('event_key', 50);
                $table->tinyInteger('is_web_enabled')->default(1);
                $table->tinyInteger('is_server_enabled')->default(1);
                $table->string('custom_event_name', 100)->nullable();
                $table->json('parameters')->nullable();
                $table->timestamps();

                $table->index('tag_manager_id', 'tag_mgr_event_idx');
                $table->unique(['tag_manager_id', 'event_key'], 'tag_mgr_event_unique');
            });

            // Try adding foreign key constraint safely
            try {
                Schema::table('tag_manager_event_configs', function (Blueprint $table) {
                    $table->foreign('tag_manager_id')
                        ->references('id')
                        ->on('google_tag_managers')
                        ->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_manager_event_configs');
    }
};
