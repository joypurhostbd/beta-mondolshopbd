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

        // Money columns -> decimal(12, 2)
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'purchase_price')) {
                    $table->decimal('purchase_price', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('products', 'old_price')) {
                    $table->decimal('old_price', 12, 2)->nullable()->change();
                }
                if (Schema::hasColumn('products', 'new_price')) {
                    $table->decimal('new_price', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'amount')) {
                    $table->decimal('amount', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('orders', 'discount')) {
                    $table->decimal('discount', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('orders', 'shipping_charge')) {
                    $table->decimal('shipping_charge', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (Schema::hasColumn('order_details', 'purchase_price')) {
                    $table->decimal('purchase_price', 12, 2)->default(0)->change();
                }
                if (Schema::hasColumn('order_details', 'sale_price')) {
                    $table->decimal('sale_price', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (Schema::hasColumn('payments', 'amount')) {
                    $table->decimal('amount', 12, 2)->default(0)->change();
                }
            });
        }

        if (Schema::hasTable('shipping_charges')) {
            Schema::table('shipping_charges', function (Blueprint $table) {
                if (Schema::hasColumn('shipping_charges', 'amount')) {
                    $table->decimal('amount', 12, 2)->default(0)->change();
                }
            });
        }

        // Float -> decimal
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (Schema::hasColumn('customers', 'balance')) {
                    $table->decimal('balance', 12, 2)->default(0)->change();
                }
            });
        }

        // Status string -> tinyInteger
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'status')) {
            try {
                DB::statement("UPDATE `customers` SET `status` = '1' WHERE `status` = 'active' OR `status` = '' OR `status` IS NULL");
                DB::statement("UPDATE `customers` SET `status` = '0' WHERE `status` = 'inactive'");
                Schema::table('customers', function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->change();
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('reviews') && Schema::hasColumn('reviews', 'status')) {
            try {
                DB::statement("UPDATE `reviews` SET `status` = '1' WHERE `status` = 'active' OR `status` = '' OR `status` IS NULL");
                DB::statement("UPDATE `reviews` SET `status` = '0' WHERE `status` = 'inactive'");
                Schema::table('reviews', function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->change();
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('campaigns') && Schema::hasColumn('campaigns', 'status')) {
            try {
                DB::statement("UPDATE `campaigns` SET `status` = '1' WHERE `status` = 'active' OR `status` = '' OR `status` IS NULL");
                DB::statement("UPDATE `campaigns` SET `status` = '0' WHERE `status` = 'inactive'");
                Schema::table('campaigns', function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->change();
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('colors') && Schema::hasColumn('colors', 'status')) {
            try {
                DB::statement("UPDATE `colors` SET `status` = '1' WHERE `status` = 'active' OR `status` = '' OR `status` IS NULL");
                DB::statement("UPDATE `colors` SET `status` = '0' WHERE `status` = 'inactive'");
                Schema::table('colors', function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->change();
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('sizes') && Schema::hasColumn('sizes', 'status')) {
            try {
                DB::statement("UPDATE `sizes` SET `status` = '1' WHERE `status` = 'active' OR `status` = '' OR `status` IS NULL");
                DB::statement("UPDATE `sizes` SET `status` = '0' WHERE `status` = 'inactive'");
                Schema::table('sizes', function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->change();
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('order_statuses') && Schema::hasColumn('order_statuses', 'status')) {
            try {
                DB::statement("UPDATE `order_statuses` SET `status` = '1' WHERE `status` = 'active' OR `status` = '' OR `status` IS NULL");
                DB::statement("UPDATE `order_statuses` SET `status` = '0' WHERE `status` = 'inactive'");
                Schema::table('order_statuses', function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->change();
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('sms_gateways') && Schema::hasColumn('sms_gateways', 'status')) {
            try {
                DB::statement("UPDATE `sms_gateways` SET `status` = '1' WHERE `status` = 'active' OR `status` = '' OR `status` IS NULL");
                DB::statement("UPDATE `sms_gateways` SET `status` = '0' WHERE `status` = 'inactive'");
                Schema::table('sms_gateways', function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->change();
                });
            } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('shipping_charges') && Schema::hasColumn('shipping_charges', 'status')) {
            try {
                DB::statement("UPDATE `shipping_charges` SET `status` = '1' WHERE `status` = 'active' OR `status` = '' OR `status` IS NULL");
                DB::statement("UPDATE `shipping_charges` SET `status` = '0' WHERE `status` = 'inactive'");
                Schema::table('shipping_charges', function (Blueprint $table) {
                    $table->tinyInteger('status')->default(1)->change();
                });
            } catch (\Throwable $e) {}
        }

        // Date string -> date
        if (Schema::hasTable('campaigns') && Schema::hasColumn('campaigns', 'date')) {
            try {
                Schema::table('campaigns', function (Blueprint $table) {
                    $table->date('date')->change();
                });
            } catch (\Throwable $e) {}
        }
    }

    public function down(): void
    {
        // No-op for forward compatibility
    }
};