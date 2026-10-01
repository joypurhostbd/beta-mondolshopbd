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
     // Create migration: php artisan make:migration add_indexes_to_orders_table
    public function up()
    {
        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('invoice_id');      
            $table->index('created_at');      
            $table->index('amount');          
            $table->index(['created_at', 'id']);
        });
        
        // Shippings table indexes
        Schema::table('shippings', function (Blueprint $table) {
            $table->index('name');      
            $table->index('phone');     
            $table->index('order_id');  
        });
    }
    
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['amount']);
            $table->dropIndex(['created_at', 'id']);
        });
        
        Schema::table('shippings', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['order_id']);
        });
    }

    
};
