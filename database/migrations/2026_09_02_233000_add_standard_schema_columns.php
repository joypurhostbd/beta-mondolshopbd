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
        // 1. Orders table
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status', 50)->default('pending')->nullable();
                }
            });
        }

        // 2. Customers table
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'remember_token')) {
                    $table->rememberToken();
                }
                if (!Schema::hasColumn('customers', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable();
                }
            });
        }

        // 3. Products table
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'sku')) {
                    $table->string('sku', 100)->nullable();
                }
                if (!Schema::hasColumn('products', 'meta_title')) {
                    $table->text('meta_title')->nullable();
                }
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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'payment_status')) {
                    $table->dropColumn('payment_status');
                }
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'remember_token')) {
                    $table->dropColumn('remember_token');
                }
                if (Schema::hasColumn('customers', 'email_verified_at')) {
                    $table->dropColumn('email_verified_at');
                }
            });
        }

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'sku')) {
                    $table->dropColumn('sku');
                }
                if (Schema::hasColumn('products', 'meta_title')) {
                    $table->dropColumn('meta_title');
                }
            });
        }
    }
};