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
                if (!Schema::hasColumn('google_tag_managers', 'is_server_side')) {
                    $table->tinyInteger('is_server_side')->default(0)->after('status');
                }
                if (!Schema::hasColumn('google_tag_managers', 'server_container_url')) {
                    $table->string('server_container_url')->nullable()->after('is_server_side');
                }
                if (!Schema::hasColumn('google_tag_managers', 'measurement_id')) {
                    $table->string('measurement_id')->nullable()->after('server_container_url');
                }
                if (!Schema::hasColumn('google_tag_managers', 'api_secret')) {
                    $table->string('api_secret')->nullable()->after('measurement_id');
                }
                if (!Schema::hasColumn('google_tag_managers', 'custom_loader_domain')) {
                    $table->tinyInteger('custom_loader_domain')->default(0)->after('api_secret');
                }
            });
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
                $columnsToDrop = [];
                foreach (['is_server_side', 'server_container_url', 'measurement_id', 'api_secret', 'custom_loader_domain'] as $column) {
                    if (Schema::hasColumn('google_tag_managers', $column)) {
                        $columnsToDrop[] = $column;
                    }
                }
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
