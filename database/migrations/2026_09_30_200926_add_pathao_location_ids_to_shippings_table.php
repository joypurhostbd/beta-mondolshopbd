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
        Schema::table('shippings', function (Blueprint $table) {
            $table->unsignedBigInteger('pathao_city_id')->nullable()->after('thana');
            $table->unsignedBigInteger('pathao_zone_id')->nullable()->after('pathao_city_id');
            $table->unsignedBigInteger('pathao_area_id')->nullable()->after('pathao_zone_id');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shippings', function (Blueprint $table) {
            $table->unsignedBigInteger('pathao_city_id')->nullable()->after('thana');
            $table->unsignedBigInteger('pathao_zone_id')->nullable()->after('pathao_city_id');
            $table->unsignedBigInteger('pathao_area_id')->nullable()->after('pathao_zone_id');
            //
        });
    }
};
