<?php

use App\Models\OrderStatus;
use Illuminate\Database\Migrations\Migration;

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

        $newStatuses = [
            ['name' => 'Courier In Review', 'slug' => 'courier-in-review'],
            ['name' => 'Courier In Transit', 'slug' => 'courier-in-transit'],
            ['name' => 'Courier Shipped', 'slug' => 'courier-shipped'],
            ['name' => 'Courier Picked', 'slug' => 'courier-picked'],
            ['name' => 'Courier Hold', 'slug' => 'courier-hold'],
            ['name' => 'Courier Returned', 'slug' => 'courier-returned'],
            ['name' => 'Courier Cancelled Approval Pending', 'slug' => 'courier-cancelled-approval-pending'],
        ];

        foreach ($newStatuses as $status) {
            OrderStatus::firstOrCreate(
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
        OrderStatus::whereIn('slug', [
            'courier-in-review',
            'courier-in-transit',
            'courier-shipped',
            'courier-picked',
            'courier-hold',
            'courier-returned',
            'courier-cancelled-approval-pending',
        ])->delete();
    }
};
