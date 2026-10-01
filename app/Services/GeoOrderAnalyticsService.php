<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\OrderStatus;
use App\Services\Support\BangladeshGeoDictionary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GeoOrderAnalyticsService
{
    /**
     * Resolve district and thana from free-text address and optional shipping area string.
     *
     * @param string|null $address
     * @param string|null $shippingArea
     * @return array{district: string|null, thana: string|null}
     */
    public function resolveFromAddress(?string $address, ?string $shippingArea = null): array
    {
        $text = mb_strtolower(trim($address ?? ''), 'UTF-8');
        $areaText = mb_strtolower(trim($shippingArea ?? ''), 'UTF-8');

        if (empty($text) && empty($areaText)) {
            return ['district' => null, 'thana' => null];
        }

        $districts = BangladeshGeoDictionary::getDistricts();
        $dhakaThanas = BangladeshGeoDictionary::getDhakaThanas();

        $matchedDistrict = null;
        $matchedThana = null;

        // 1. Scan for District
        foreach ($districts as $districtName => $data) {
            foreach ($data['aliases'] as $alias) {
                if (mb_strpos($text, $alias) !== false) {
                    $matchedDistrict = $districtName;
                    break 2;
                }
            }
        }

        // 2. Scan for Thana/Area
        if ($matchedDistrict && isset($districts[$matchedDistrict])) {
            $mainLower = mb_strtolower($matchedDistrict, 'UTF-8');
            foreach ($districts[$matchedDistrict]['aliases'] as $alias) {
                if ($alias !== $mainLower && !in_array($alias, ['ঢাকা', 'চট্টগ্রাম', 'বগুড়া', 'খুলনা', 'সিলেট', 'রাজশাহী', 'রংপুর', 'বরিশাল', 'কুমিল্লা', 'ময়মনসিংহ', 'কক্সবাজার'], true)) {
                    if (mb_strpos($text, $alias) !== false) {
                        $matchedThana = mb_convert_case($alias, MB_CASE_TITLE, 'UTF-8');
                        break;
                    }
                }
            }
        }

        // 3. Scan Dhaka areas if district is Dhaka or unset
        if (!$matchedThana && (!$matchedDistrict || $matchedDistrict === 'Dhaka')) {
            foreach ($dhakaThanas as $thanaName => $aliases) {
                foreach ($aliases as $alias) {
                    if (mb_strpos($text, $alias) !== false) {
                        $matchedThana = $thanaName;
                        if (!$matchedDistrict) {
                            $matchedDistrict = 'Dhaka';
                        }
                        break 2;
                    }
                }
            }
        }

        // 4. Fallback scan if thana and district still null
        if (!$matchedThana && !$matchedDistrict) {
            foreach ($districts as $districtName => $data) {
                $mainLower = mb_strtolower($districtName, 'UTF-8');
                foreach ($data['aliases'] as $alias) {
                    if ($alias !== $mainLower && mb_strpos($text, $alias) !== false) {
                        $matchedDistrict = $districtName;
                        $matchedThana = mb_convert_case($alias, MB_CASE_TITLE, 'UTF-8');
                        break 2;
                    }
                }
            }
        }

        // 5. Fallback: If shipping area label indicates inside Dhaka
        if (!$matchedDistrict && (str_contains($areaText, 'ঢাকার ভিতরে') || str_contains($areaText, 'inside dhaka'))) {
            $matchedDistrict = 'Dhaka';
        }

        return [
            'district' => $matchedDistrict,
            'thana' => $matchedThana,
        ];
    }

    /**
     * Get aggregated geographic overview & KPIs for dashboard.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getGeographicOverview(array $filters = []): array
    {
        $query = DB::table('shippings')
            ->join('orders', 'orders.id', '=', 'shippings.order_id');

        $this->applyDateFilter($query, $filters);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('orders.order_status', (int) $filters['status']);
        }

        $totalOrders = (clone $query)->count();

        $geocodedQuery = (clone $query)->whereNotNull('shippings.district')->where('shippings.district', '!=', '');
        $geocodedOrders = (clone $geocodedQuery)->count();
        $geocodedRate = $totalOrders > 0 ? round(($geocodedOrders / $totalOrders) * 100, 1) : 0.0;

        $insideDhakaCount = (clone $query)->where('shippings.district', 'Dhaka')->count();
        $outsideDhakaCount = $totalOrders - $insideDhakaCount;
        $insideDhakaPct = $totalOrders > 0 ? round(($insideDhakaCount / $totalOrders) * 100, 1) : 0.0;
        $outsideDhakaPct = $totalOrders > 0 ? round(($outsideDhakaCount / $totalOrders) * 100, 1) : 0.0;

        $deliveredStatusIds = OrderStatus::whereIn('slug', ['completed', 'delivered', 'courier-delivered'])->pluck('id')->toArray();
        $deliveredStatusIn = !empty($deliveredStatusIds) ? implode(',', $deliveredStatusIds) : '6';

        $topDistricts = (clone $geocodedQuery)
            ->select(
                'shippings.district',
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('SUM(orders.amount) as total_amount'),
                DB::raw('SUM(CASE WHEN orders.order_status IN (' . $deliveredStatusIn . ') THEN 1 ELSE 0 END) as delivered_count')
            )
            ->groupBy('shippings.district')
            ->orderByDesc('order_count')
            ->limit(15)
            ->get()
            ->map(function ($item) use ($totalOrders) {
                $count = (int) $item->order_count;
                $delivered = (int) $item->delivered_count;
                return [
                    'district' => $item->district,
                    'order_count' => $count,
                    'total_amount' => (float) $item->total_amount,
                    'delivered_count' => $delivered,
                    'delivery_rate' => $count > 0 ? round(($delivered / $count) * 100, 1) : 0.0,
                    'share_percentage' => $totalOrders > 0 ? round(($count / $totalOrders) * 100, 1) : 0.0,
                ];
            });

        $thanasQuery = (clone $query)
            ->whereNotNull('shippings.thana')
            ->where('shippings.thana', '!=', '')
            ->select(
                'shippings.thana',
                'shippings.district',
                DB::raw('COUNT(orders.id) as order_count'),
                DB::raw('SUM(orders.amount) as total_amount'),
                DB::raw('SUM(CASE WHEN orders.order_status IN (' . $deliveredStatusIn . ') THEN 1 ELSE 0 END) as delivered_count')
            );

        if (!empty($filters['district']) && $filters['district'] !== 'all') {
            $thanasQuery->where('shippings.district', $filters['district']);
        }

        $topThanas = $thanasQuery
            ->groupBy('shippings.thana', 'shippings.district')
            ->orderByDesc('order_count')
            ->limit(15)
            ->get()
            ->map(function ($item) use ($totalOrders) {
                $count = (int) $item->order_count;
                $delivered = (int) $item->delivered_count;
                return [
                    'thana' => $item->thana,
                    'district' => $item->district ?? 'Unassigned',
                    'order_count' => $count,
                    'total_amount' => (float) $item->total_amount,
                    'delivered_count' => $delivered,
                    'delivery_rate' => $count > 0 ? round(($delivered / $count) * 100, 1) : 0.0,
                    'share_percentage' => $totalOrders > 0 ? round(($count / $totalOrders) * 100, 1) : 0.0,
                ];
            });

        $topDistrict = $topDistricts->first();
        $topThana = $topThanas->first();

        return [
            'kpis' => [
                'total_orders' => $totalOrders,
                'geocoded_orders' => $geocodedOrders,
                'geocoded_rate' => $geocodedRate,
                'top_district' => $topDistrict ? $topDistrict['district'] : 'N/A',
                'top_district_count' => $topDistrict ? $topDistrict['order_count'] : 0,
                'top_district_amount' => $topDistrict ? $topDistrict['total_amount'] : 0,
                'top_thana' => $topThana ? $topThana['thana'] : 'N/A',
                'top_thana_count' => $topThana ? $topThana['order_count'] : 0,
                'top_thana_district' => $topThana ? $topThana['district'] : '',
                'inside_dhaka_count' => $insideDhakaCount,
                'inside_dhaka_pct' => $insideDhakaPct,
                'outside_dhaka_count' => $outsideDhakaCount,
                'outside_dhaka_pct' => $outsideDhakaPct,
            ],
            'top_districts' => $topDistricts,
            'top_thanas' => $topThanas,
            'available_districts' => array_keys(BangladeshGeoDictionary::getDistricts()),
        ];
    }

    /**
     * Apply time period or date range filter to the query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param array<string, mixed> $filters
     * @return void
     */
    protected function applyDateFilter($query, array $filters): void
    {
        $period = $filters['period'] ?? 'all_time';

        switch ($period) {
            case 'today':
                $query->whereDate('orders.created_at', Carbon::today());
                break;
            case 'yesterday':
                $query->whereDate('orders.created_at', Carbon::yesterday());
                break;
            case 'last_7_days':
                $query->where('orders.created_at', '>=', Carbon::now()->subDays(7));
                break;
            case 'this_month':
                $query->whereMonth('orders.created_at', Carbon::now()->month)
                      ->whereYear('orders.created_at', Carbon::now()->year);
                break;
            case 'last_month':
                $lastMonth = Carbon::now()->subMonth();
                $query->whereMonth('orders.created_at', $lastMonth->month)
                      ->whereYear('orders.created_at', $lastMonth->year);
                break;
            case 'custom':
                if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                    $query->whereBetween('orders.created_at', [
                        Carbon::parse($filters['start_date'])->startOfDay(),
                        Carbon::parse($filters['end_date'])->endOfDay()
                    ]);
                }
                break;
            case 'all_time':
            default:
                break;
        }
    }
}
