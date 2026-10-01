<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Modules\Shipping\Application\Actions\SyncCourierOrderStatusAction;

class SyncCourierStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courier:sync-status 
                            {--courier=steadfast : Target courier provider} 
                            {--limit=100 : Maximum number of active orders to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll and synchronize live delivery status for active dispatched orders from courier API';

    /**
     * Execute the console command.
     */
    public function handle(SyncCourierOrderStatusAction $syncAction): int
    {
        $courier = strtolower((string) $this->option('courier'));
        $limit = (int) $this->option('limit');

        $this->info("Scanning up to {$limit} active order(s) for courier [{$courier}]...");

        $terminalStatuses = ['delivered', 'cancelled', 'returned', 'return'];

        $query = Order::query()
            ->where(function ($q) {
                $q->whereNotNull('consignment_id')->where('consignment_id', '!=', '')
                  ->orWhereNotNull('tracking_code')->where('tracking_code', '!=', '');
            })
            ->where(function ($q) use ($terminalStatuses) {
                $q->whereNull('courier_status')
                  ->orWhereNotIn('courier_status', $terminalStatuses);
            });

        if ($courier !== 'all') {
            $query->where(function ($q) use ($courier) {
                $q->whereNull('courier_name')
                  ->orWhere('courier_name', $courier);
            });
        }

        $orders = $query->latest()->limit($limit)->get();

        if ($orders->isEmpty()) {
            $this->info('No active in-transit orders found to sync.');
            return Command::SUCCESS;
        }

        $this->info("Found {$orders->count()} order(s) to synchronize.");

        $successCount = 0;
        $changedCount = 0;
        $failedCount = 0;

        foreach ($orders as $order) {
            try {
                $result = $syncAction->execute($order);
                if ($result['success']) {
                    $successCount++;
                    if (!empty($result['status_changed'])) {
                        $changedCount++;
                        $this->line(" - Order #{$order->invoice_id}: Status updated to [{$result['courier_status']}].");
                    }
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error(" - Order #{$order->invoice_id} sync error: {$e->getMessage()}");
            }
        }

        $this->info("Sync complete. {$successCount} checked, {$changedCount} updated, {$failedCount} failed.");

        return Command::SUCCESS;
    }
}
