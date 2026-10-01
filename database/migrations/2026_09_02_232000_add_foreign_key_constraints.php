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
        // Subcategories foreign key
        if (Schema::hasTable('subcategories') && Schema::hasTable('categories')) {
            try {
                Schema::table('subcategories', function (Blueprint $table) {
                    $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }

        // Childcategories foreign key
        if (Schema::hasTable('childcategories') && Schema::hasTable('subcategories')) {
            try {
                Schema::table('childcategories', function (Blueprint $table) {
                    $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }

        // Order Details foreign key
        if (Schema::hasTable('order_details') && Schema::hasTable('orders')) {
            try {
                Schema::table('order_details', function (Blueprint $table) {
                    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }

        // Shippings foreign key
        if (Schema::hasTable('shippings') && Schema::hasTable('orders')) {
            try {
                Schema::table('shippings', function (Blueprint $table) {
                    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }

        // Payments foreign key
        if (Schema::hasTable('payments') && Schema::hasTable('orders')) {
            try {
                Schema::table('payments', function (Blueprint $table) {
                    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }

        // Product Images foreign key
        if (Schema::hasTable('productimages') && Schema::hasTable('products')) {
            try {
                Schema::table('productimages', function (Blueprint $table) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }

        // Product Colors foreign key
        if (Schema::hasTable('productcolors') && Schema::hasTable('products')) {
            try {
                Schema::table('productcolors', function (Blueprint $table) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }

        // Product Sizes foreign key
        if (Schema::hasTable('productsizes') && Schema::hasTable('products')) {
            try {
                Schema::table('productsizes', function (Blueprint $table) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }

        // Reviews foreign key
        if (Schema::hasTable('reviews') && Schema::hasTable('products')) {
            try {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                });
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('subcategories')) {
            Schema::table('subcategories', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
            });
        }

        if (Schema::hasTable('childcategories')) {
            Schema::table('childcategories', function (Blueprint $table) {
                $table->dropForeign(['subcategory_id']);
            });
        }

        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
            });
        }

        if (Schema::hasTable('shippings')) {
            Schema::table('shippings', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
            });
        }

        if (Schema::hasTable('productimages')) {
            Schema::table('productimages', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        if (Schema::hasTable('productcolors')) {
            Schema::table('productcolors', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        if (Schema::hasTable('productsizes')) {
            Schema::table('productsizes', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }

        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropForeign(['product_id']);
            });
        }
    }
};