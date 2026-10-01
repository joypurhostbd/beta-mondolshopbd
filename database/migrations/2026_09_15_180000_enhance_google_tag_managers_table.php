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
    public function up(): void
    {
        if (Schema::hasTable('google_tag_managers')) {
            Schema::table('google_tag_managers', function (Blueprint $table) {
                if (!Schema::hasColumn('google_tag_managers', 'title')) {
                    $table->string('title')->nullable()->after('id');
                }
                if (!Schema::hasColumn('google_tag_managers', 'description')) {
                    $table->text('description')->nullable()->after('status');
                }
            });

            try {
                Schema::table('google_tag_managers', function (Blueprint $table) {
                    $table->index('status', 'google_tag_managers_status_index');
                });
            } catch (\Throwable $e) {
                // Index may already exist
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        if (Schema::hasTable('google_tag_managers')) {
            Schema::table('google_tag_managers', function (Blueprint $table) {
                try {
                    $table->dropIndex('google_tag_managers_status_index');
                } catch (\Throwable $e) {
                    // Index may not exist
                }

                if (Schema::hasColumn('google_tag_managers', 'title')) {
                    $table->dropColumn('title');
                }
                if (Schema::hasColumn('google_tag_managers', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};
