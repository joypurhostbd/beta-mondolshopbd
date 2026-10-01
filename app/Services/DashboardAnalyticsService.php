<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    /**
     * Resolve status IDs dynamically mapped by their slug from the database.
     * Guarantees 100% environment resilience across Production MariaDB and Test SQLite.
     *
     * @return array<string, int|string>
     */
    public function getStatusMap(): array
    {
        return OrderStatus::where('status', 1)->pluck('id', 'slug')->toArray();
    }

    /**
     * Get aggregate dashboard summary metrics in a single SARGable SQL execution.
     *
     * @return array<string, mixed>
     */
    public function getDashboardSummary(): array
    {
        $statusMap = $this->getStatusMap();

        $pendingId = (int) ($statusMap['pending'] ?? 1);
        $processingIds = array_values(array_filter([
            $statusMap['processing'] ?? 2,
            $statusMap['packing'] ?? 9,
        ]));
        $shippedIds = array_values(array_filter([
            $statusMap['in-courier'] ?? 5,
            $statusMap['on-the-way'] ?? 3,
            $statusMap['courier-shipped'] ?? null,
            $statusMap['courier-in-transit'] ?? null,
            $statusMap['courier-picked'] ?? null,
        ]));
        $deliveredIds = array_values(array_filter([
            $statusMap['completed'] ?? 6,
            $statusMap['delivered'] ?? null,
            $statusMap['courier-delivered'] ?? null,
        ]));
        $cancelledIds = array_values(array_filter([
            $statusMap['cancelled'] ?? 7,
            $statusMap['courier-cancel'] ?? null,
        ]));

        $todayStart = Carbon::today()->toDateTimeString();

        $metrics = Order::selectRaw("
            COUNT(*) as total_orders,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as today_orders,
            SUM(CASE WHEN order_status = ? THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN order_status IN (" . implode(',', $processingIds) . ") THEN 1 ELSE 0 END) as processing_orders,
            SUM(CASE WHEN order_status IN (" . implode(',', $shippedIds) . ") OR LOWER(TRIM(COALESCE(courier_status, ''))) IN ('shipped', 'in_transit', 'in-transit', 'picked') THEN 1 ELSE 0 END) as shipped_orders,
            SUM(CASE WHEN order_status IN (" . implode(',', $deliveredIds) . ") OR LOWER(TRIM(COALESCE(courier_status, ''))) IN ('delivered', 'completed', 'successful') THEN 1 ELSE 0 END) as delivered_orders,
            SUM(CASE WHEN created_at >= ? AND (order_status IN (" . implode(',', $deliveredIds) . ") OR LOWER(TRIM(COALESCE(courier_status, ''))) IN ('delivered', 'completed', 'successful')) THEN 1 ELSE 0 END) as today_delivered_orders,
            SUM(CASE WHEN order_status IN (" . implode(',', $cancelledIds) . ") OR LOWER(TRIM(COALESCE(courier_status, ''))) IN ('cancelled', 'canceled', 'cancel') THEN 1 ELSE 0 END) as cancelled_orders,
            SUM(CASE WHEN order_status NOT IN (" . implode(',', $cancelledIds) . ") THEN amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN created_at >= ? AND order_status NOT IN (" . implode(',', $cancelledIds) . ") THEN amount ELSE 0 END) as today_revenue
        ", [$todayStart, $pendingId, $todayStart, $todayStart])->first();

        $total_order = (int) ($metrics->total_orders ?? 0);
        $today_order = (int) ($metrics->today_orders ?? 0);
        $pending_order = (int) ($metrics->pending_orders ?? 0);
        $processing_order = (int) ($metrics->processing_orders ?? 0);
        $shipped_order = (int) ($metrics->shipped_orders ?? 0);
        $total_delivery = (int) ($metrics->delivered_orders ?? 0);
        $today_delivery = (int) ($metrics->today_delivered_orders ?? 0);
        $total_cancelled = (int) ($metrics->cancelled_orders ?? 0);

        $total_processed = $total_delivery + $total_cancelled;
        $delivery_rate = $total_processed > 0 ? round(($total_delivery / $total_processed) * 100, 1) : 100.0;
        $return_rate = $total_processed > 0 ? round(($total_cancelled / $total_processed) * 100, 1) : 0.0;

        $total_revenue = (float) ($metrics->total_revenue ?? 0.0);
        $today_revenue = (float) ($metrics->today_revenue ?? 0.0);

        $courier_pipeline = [
            'pending' => $pending_order,
            'processing' => $processing_order,
            'shipped' => $shipped_order,
            'delivered' => $total_delivery,
        ];

        $incomplete_order = IncompleteOrder::count();
        $total_product = Product::count();
        $total_customer = Customer::count();
        $low_stock_count = Product::where('status', 1)->where('stock', '<=', 5)->count();

        return [
            'total_order' => $total_order,
            'today_order' => $today_order,
            'pending_order' => $pending_order,
            'processing_order' => $processing_order,
            'total_delivery' => $total_delivery,
            'today_delivery' => $today_delivery,
            'total_cancelled' => $total_cancelled,
            'delivery_rate' => $delivery_rate,
            'return_rate' => $return_rate,
            'total_revenue' => $total_revenue,
            'today_revenue' => $today_revenue,
            'courier_pipeline' => $courier_pipeline,
            'incomplete_order' => $incomplete_order,
            'total_product' => $total_product,
            'total_customer' => $total_customer,
            'low_stock_count' => $low_stock_count,
        ];
    }

    /**
     * Get chronologically ordered daily sales analytics (Gross Sales and Delivered Sales).
     *
     * @param int $days
     * @return Collection
     */
    public function getMonthlyDeliveredSales(int $days = 30): Collection
    {
        $statusMap = $this->getStatusMap();
        $deliveredIds = array_values(array_filter([
            $statusMap['completed'] ?? 6,
            $statusMap['delivered'] ?? null,
            $statusMap['courier-delivered'] ?? null,
        ]));
        $cancelledIds = array_values(array_filter([
            $statusMap['cancelled'] ?? 7,
            $statusMap['courier-cancel'] ?? null,
        ]));

        $cancelledIn = !empty($cancelledIds) ? implode(',', $cancelledIds) : '7';
        $deliveredIn = !empty($deliveredIds) ? implode(',', $deliveredIds) : '6';

        return Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(CASE WHEN order_status NOT IN (' . $cancelledIn . ') THEN amount ELSE 0 END) as amount'),
            DB::raw('SUM(CASE WHEN order_status IN (' . $deliveredIn . ') THEN amount ELSE 0 END) as delivered_amount'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(CASE WHEN order_status IN (' . $deliveredIn . ') THEN 1 ELSE 0 END) as delivered_orders')
        )
            ->whereRaw('order_status NOT IN (' . $cancelledIn . ')')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit($days)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Get top-selling products by quantity and revenue.
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopSellingProducts(int $limit = 5): Collection
    {
        return OrderDetails::select(
            'product_id',
            'product_name',
            DB::raw('SUM(qty) as total_sold'),
            DB::raw('SUM(qty * sale_price) as total_amount')
        )
            ->whereNotNull('product_id')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sold')
            ->with('image')
            ->limit($limit)
            ->get();
    }

    /**
     * Get products with low stock count.
     *
     * @param int $threshold
     * @param int $limit
     * @return Collection
     */
    public function getLowStockProducts(int $threshold = 5, int $limit = 10): Collection
    {
        return Product::where('status', 1)
            ->where('stock', '<=', $threshold)
            ->with('image')
            ->orderBy('stock', 'asc')
            ->limit($limit)
            ->get();
    }
}
