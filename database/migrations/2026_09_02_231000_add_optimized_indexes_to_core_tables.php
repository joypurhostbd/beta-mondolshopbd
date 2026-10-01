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
        // Products table indexes
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('category_id');
                $table->index('subcategory_id');
                $table->index('childcategory_id');
                $table->index('brand_id');
                $table->index('status');
                $table->index('topsale');
                $table->index('slug');
                $table->index(['status', 'category_id']);
            });
        }

        // Order Details table indexes
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->index('order_id');
                $table->index('product_id');
            });
        }

        // Customers table indexes
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->index('phone');
                $table->index('status');
            });
        }

        // Categories table indexes
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->index('slug');
                $table->index('status');
            });
        }

        // Subcategories table indexes
        if (Schema::hasTable('subcategories')) {
            Schema::table('subcategories', function (Blueprint $table) {
                $table->index('category_id');
                $table->index('slug');
                $table->index('status');
            });
        }

        // Childcategories table indexes
        if (Schema::hasTable('childcategories')) {
            Schema::table('childcategories', function (Blueprint $table) {
                $table->index('subcategory_id');
                $table->index('slug');
                $table->index('status');
            });
        }

        // Reviews table indexes
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index('product_id');
                $table->index('customer_id');
                $table->index('status');
            });
        }

        // Incomplete orders table indexes
        if (Schema::hasTable('incomplete_orders')) {
            Schema::table('incomplete_orders', function (Blueprint $table) {
                $table->index('phone');
                $table->index('created_at');
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
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['category_id']);
                $table->dropIndex(['subcategory_id']);
                $table->dropIndex(['childcategory_id']);
                $table->dropIndex(['brand_id']);
                $table->dropIndex(['status']);
                $table->dropIndex(['topsale']);
                $table->dropIndex(['slug']);
                $table->dropIndex(['status', 'category_id']);
            });
        }

        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->dropIndex(['order_id']);
                $table->dropIndex(['product_id']);
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['phone']);
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex(['slug']);
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('subcategories')) {
            Schema::table('subcategories', function (Blueprint $table) {
                $table->dropIndex(['category_id']);
                $table->dropIndex(['slug']);
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('childcategories')) {
            Schema::table('childcategories', function (Blueprint $table) {
                $table->dropIndex(['subcategory_id']);
                $table->dropIndex(['slug']);
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropIndex(['product_id']);
                $table->dropIndex(['customer_id']);
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('incomplete_orders')) {
            Schema::table('incomplete_orders', function (Blueprint $table) {
                $table->dropIndex(['phone']);
                $table->dropIndex(['created_at']);
            });
        }
    }
};