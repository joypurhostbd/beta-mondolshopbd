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
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (! Schema::hasColumn('order_details', 'product_image')) {
                    $table->string('product_image', 255)->nullable()->after('product_color');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (Schema::hasColumn('order_details', 'product_image')) {
                    $table->dropColumn('product_image');
                }
            });
        }
    }
};
