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
        if (app()->environment('testing')) {
            return;
        }

        $statuses = [
            ['name' => 'Packing', 'slug' => 'packing'],
            ['name' => 'In Review', 'slug' => 'in-review'],
            ['name' => 'Courier Pending', 'slug' => 'courier-pending'],
            ['name' => 'Courier Partial', 'slug' => 'courier-partial'],
            ['name' => 'Courier Cancel', 'slug' => 'courier-cancel'],
            ['name' => 'Courier Delivered', 'slug' => 'courier-delivered'],
        ];

        foreach ($statuses as $status) {
            \App\Models\OrderStatus::firstOrCreate(
                ['slug' => $status['slug']],
                ['name' => $status['name'], 'status' => 1]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\OrderStatus::whereIn('slug', [
            'courier-pending',
            'courier-partial',
            'courier-cancel',
            'courier-delivered',
        ])->delete();
    }
};
