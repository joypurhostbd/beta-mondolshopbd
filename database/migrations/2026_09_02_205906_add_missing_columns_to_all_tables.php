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
        // 1. campaigns
        if (Schema::hasTable('campaigns')) {
            Schema::table('campaigns', function (Blueprint $table) {
                if (!Schema::hasColumn('campaigns', 'product_id')) {
                    $table->integer('product_id')->nullable();
                }
            });
        }

        // 2. categories
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (!Schema::hasColumn('categories', 'meta_description')) {
                    $table->text('meta_description')->nullable();
                }
                if (!Schema::hasColumn('categories', 'front_view')) {
                    $table->integer('front_view')->nullable();
                }
            });
        }

        // 3. childcategories
        if (Schema::hasTable('childcategories')) {
            Schema::table('childcategories', function (Blueprint $table) {
                if (!Schema::hasColumn('childcategories', 'meta_description')) {
                    $table->longText('meta_description')->nullable();
                }
            });
        }

        // 4. courierapis
        if (Schema::hasTable('courierapis')) {
            Schema::table('courierapis', function (Blueprint $table) {
                if (!Schema::hasColumn('courierapis', 'status')) {
                    $table->string('status', 11)->nullable();
                }
            });
        }

        // 5. customers
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                if (!Schema::hasColumn('customers', 'forgot')) {
                    $table->string('forgot', 11)->nullable();
                }
            });
        }

        // 6. general_settings
        if (Schema::hasTable('general_settings')) {
            Schema::table('general_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('general_settings', 'description')) {
                    $table->longText('description')->nullable();
                }
            });
        }

        // 7. orders
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'ip_address')) {
                    $table->string('ip_address', 30)->nullable();
                }
                if (!Schema::hasColumn('orders', 'admin_note')) {
                    $table->text('admin_note')->nullable();
                }
                if (!Schema::hasColumn('orders', 'courier_name')) {
                    $table->string('courier_name', 255)->nullable();
                }
                if (!Schema::hasColumn('orders', 'courier_status')) {
                    $table->string('courier_status', 255)->nullable();
                }
                if (!Schema::hasColumn('orders', 'user_id')) {
                    $table->integer('user_id')->nullable();
                }
                if (!Schema::hasColumn('orders', 'note')) {
                    $table->string('note', 256)->nullable();
                }
                if (!Schema::hasColumn('orders', 'f_check')) {
                    $table->string('f_check', 255)->nullable();
                }
            });
        }

        // 8. order_details
        if (Schema::hasTable('order_details')) {
            Schema::table('order_details', function (Blueprint $table) {
                if (!Schema::hasColumn('order_details', 'product_discount')) {
                    $table->integer('product_discount')->default(0)->nullable();
                }
                if (!Schema::hasColumn('order_details', 'product_size')) {
                    $table->string('product_size', 255)->nullable();
                }
                if (!Schema::hasColumn('order_details', 'product_color')) {
                    $table->string('product_color', 255)->nullable();
                }
            });
        }

        // 9. payment_gateways
        if (Schema::hasTable('payment_gateways')) {
            Schema::table('payment_gateways', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_gateways', 'status')) {
                    $table->tinyInteger('status')->default(0);
                }
            });
        }

        // 10. products
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'subcategory_id')) {
                    $table->integer('subcategory_id')->nullable();
                }
                if (!Schema::hasColumn('products', 'childcategory_id')) {
                    $table->integer('childcategory_id')->nullable();
                }
                if (!Schema::hasColumn('products', 'pro_unit')) {
                    $table->string('pro_unit', 191)->nullable();
                }
                if (!Schema::hasColumn('products', 'pro_video')) {
                    $table->string('pro_video', 255)->nullable();
                }
            });
        }

        // 11. reviews
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (!Schema::hasColumn('reviews', 'customer_id')) {
                    $table->integer('customer_id')->nullable();
                }
            });
        }

        // 12. sms_gateways
        if (Schema::hasTable('sms_gateways')) {
            Schema::table('sms_gateways', function (Blueprint $table) {
                if (!Schema::hasColumn('sms_gateways', 'order')) {
                    $table->string('order', 11)->nullable();
                }
                if (!Schema::hasColumn('sms_gateways', 'forget_pass')) {
                    $table->string('forget_pass', 11)->nullable();
                }
                if (!Schema::hasColumn('sms_gateways', 'password_g')) {
                    $table->string('password_g', 11)->nullable();
                }
            });
        }

        // 13. social_media
        if (Schema::hasTable('social_media')) {
            Schema::table('social_media', function (Blueprint $table) {
                if (!Schema::hasColumn('social_media', 'link')) {
                    $table->string('link', 155)->nullable();
                }
                if (!Schema::hasColumn('social_media', 'color')) {
                    $table->string('color', 20)->nullable();
                }
            });
        }

        // 14. subcategories
        if (Schema::hasTable('subcategories')) {
            Schema::table('subcategories', function (Blueprint $table) {
                if (!Schema::hasColumn('subcategories', 'meta_description')) {
                    $table->longText('meta_description')->nullable();
                }
            });
        }

        // 15. users
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'image')) {
                    $table->string('image', 255)->nullable();
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
        // Safe rollback if needed
    }
};
