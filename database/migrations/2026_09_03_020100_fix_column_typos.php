<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'meta_decription') && !Schema::hasColumn('categories', 'meta_description')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->renameColumn('meta_decription', 'meta_description');
            });
        }

        if (Schema::hasTable('subcategories') && Schema::hasColumn('subcategories', 'meta_decription') && !Schema::hasColumn('subcategories', 'meta_description')) {
            Schema::table('subcategories', function (Blueprint $table) {
                $table->renameColumn('meta_decription', 'meta_description');
            });
        }

        if (Schema::hasTable('childcategories') && Schema::hasColumn('childcategories', 'meta_decription') && !Schema::hasColumn('childcategories', 'meta_description')) {
            Schema::table('childcategories', function (Blueprint $table) {
                $table->renameColumn('meta_decription', 'meta_description');
            });
        }

        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'ratting') && !Schema::hasColumn('reviews', 'rating')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->renameColumn('ratting', 'rating');
            });
        }

        if (Schema::hasTable('sms_gateways') && Schema::hasColumn('sms_gateways', 'serderid') && !Schema::hasColumn('sms_gateways', 'sender_id')) {
            Schema::table('sms_gateways', function (Blueprint $table) {
                $table->renameColumn('serderid', 'sender_id');
            });
        }
    }

    public function down(): void
    {
        // No-op
    }
};