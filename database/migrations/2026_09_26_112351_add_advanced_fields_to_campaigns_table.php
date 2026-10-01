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
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'offer_title')) {
                $table->string('offer_title', 255)->nullable()->after('name');
            }
            if (!Schema::hasColumn('campaigns', 'video_url')) {
                $table->string('video_url', 500)->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('campaigns', 'start_date')) {
                $table->timestamp('start_date')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('campaigns', 'end_date')) {
                $table->timestamp('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('campaigns', 'special_price')) {
                $table->decimal('special_price', 10, 2)->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('campaigns', 'free_shipping')) {
                $table->boolean('free_shipping')->default(0)->after('special_price');
            }
            if (!Schema::hasColumn('campaigns', 'meta_title')) {
                $table->string('meta_title', 255)->nullable()->after('status');
            }
            if (!Schema::hasColumn('campaigns', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('campaigns', 'meta_keywords')) {
                $table->string('meta_keywords', 255)->nullable()->after('meta_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $columnsToDrop = [
                'video_url',
                'offer_title',
                'start_date',
                'end_date',
                'special_price',
                'free_shipping',
                'meta_title',
                'meta_description',
                'meta_keywords',
            ];
            foreach ($columnsToDrop as $col) {
                if (Schema::hasColumn('campaigns', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
