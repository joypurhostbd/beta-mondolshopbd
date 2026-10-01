@props([
    'order' => null,
    'stats' => null,
    'mode' => 'compact', // 'compact', 'badge', 'card'
])

@php
    $orderModel = is_object($order) ? $order : null;
    $orderId = $orderModel?->id ?? (is_array($order) ? ($order['id'] ?? null) : null);
    
    // Resolve normalized stats
    if (is_array($stats) && !empty($stats)) {
        $dataStats = isset($stats['total_stats']) ? array_merge($stats['total_stats'], [
            'courier_breakdown' => $stats['courier_breakdown'] ?? [],
            'has_data' => true,
        ]) : $stats;
    } elseif (isset($fraudCheckData) && is_array($fraudCheckData) && !empty($fraudCheckData['total_stats'])) {
        $dataStats = array_merge($fraudCheckData['total_stats'], [
            'courier_breakdown' => $fraudCheckData['courier_breakdown'] ?? [],
            'has_data' => true,
        ]);
    } elseif ($orderModel) {
        $dataStats = $orderModel->fraud_report_stats;
    } elseif (is_array($order) && isset($order['fraud_report_stats'])) {
        $dataStats = $order['fraud_report_stats'];
    } else {
        $dataStats = [
            'total_parcel' => 0,
            'total_delivered' => 0,
            'total_cancel' => 0,
            'delivery_rate' => 0,
            'has_data' => false,
            'courier_breakdown' => [],
        ];
    }

    $total = (int) ($dataStats['total_parcel'] ?? $dataStats['total_orders'] ?? 0);
    $delivered = (int) ($dataStats['total_delivered'] ?? 0);
    $canceled = (int) ($dataStats['total_cancel'] ?? 0);
    $rate = (float) ($dataStats['delivery_rate'] ?? 0);
    $hasScore = ($dataStats['has_data'] ?? false) || ($rate > 0) || ($total > 0);

    $badgeClass = $rate >= 80 ? 'bg-success' : ($rate >= 50 ? 'bg-warning' : 'bg-danger');
    $textColor = $rate >= 80 ? 'text-success' : ($rate >= 50 ? 'text-warning' : 'text-danger');
    $circleColor = $rate >= 80 ? '#10b981' : ($rate >= 50 ? '#f59e0b' : '#ef4444');

    $defaultBreakdown = [
        'Steadfast' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0],
        'RedX' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0],
        'Pathao' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0],
        'Carrybee' => ['orders' => 0, 'delivered' => 0, 'canceled' => 0, 'return_rate' => 0],
    ];

    $courierBreakdown = !empty($dataStats['courier_breakdown']) ? $dataStats['courier_breakdown'] : $defaultBreakdown;

    // Tooltip breakdown summary
    $breakdownParts = [];
    foreach ($courierBreakdown as $cName => $cData) {
        $cOrders = (int) ($cData['orders'] ?? $cData['Total Parcels'] ?? 0);
        $cDelivered = (int) ($cData['delivered'] ?? $cData['Delivered Parcels'] ?? 0);
        if ($cOrders > 0) {
            $breakdownParts[] = "{$cName}: {$cDelivered}/{$cOrders}";
        }
    }
    $tooltipTitle = !empty($breakdownParts) ? 'Breakdown: ' . implode(' | ', $breakdownParts) : "Order Guard Delivery Rate: {$rate}%";
    
    // Gauge mathematics
    $circumference = 282.743;
    $dashOffset = $hasScore ? ($circumference - ($circumference * (min(100, max(0, $rate)) / 100))) : $circumference;
@endphp

@if($mode === 'compact')
    {{-- Mode 1: Compact View for DataTables and Order Lists --}}
    <div class="order-guard-box" id="order-guard-{{ $orderId }}" data-order-id="{{ $orderId }}" style="min-width: 165px; max-width: 195px;">
        <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
            <div class="order-guard-progress flex-grow-1" style="height: 7px; background-color: #e2e8f0; border-radius: 4px; overflow: hidden;">
                <div class="order-guard-progress-bar" style="width: {{ $rate }}%; height: 100%; background: linear-gradient(90deg, #6366f1, #5e35b1); border-radius: 4px; transition: width 0.4s ease;" aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <button type="button" class="btn btn-xs btn-link p-0 text-muted btn-refresh-order-guard ms-1" data-order-id="{{ $orderId }}" title="Re-check Courier Delivery Report">
                <i class="fe-refresh-cw font-12"></i>
            </button>
            <span class="order-guard-logo ms-1 d-inline-flex align-items-center cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $tooltipTitle }}">
                <svg width="18" height="13" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 7L10 3L21 6L12 10L2 7Z" stroke="#475569" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M6 12L10 19L22 5" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
        <div class="order-guard-stats text-center font-11 fw-semibold text-nowrap" style="letter-spacing: -0.2px;">
            <span class="text-secondary">ALL: <strong class="guard-all text-dark">{{ $total }}</strong></span>
            <span class="text-muted mx-1">|</span>
            <span class="text-success">DLVD: <strong class="guard-dlvd">{{ $delivered }}</strong></span>
            <span class="text-muted mx-1">|</span>
            <span class="text-danger">CANCL: <strong class="guard-cancl">{{ $canceled }}</strong></span>
        </div>
    </div>

@elseif($mode === 'badge')
    {{-- Mode 2: Synchronized Header Badge for Order Edit & Quick Cards --}}
    <div class="text-end d-flex align-items-center gap-1 order-guard-badge-wrapper" id="order-delivery-success-wrapper" data-order-id="{{ $orderId }}">
        <div>
            <small class="text-muted font-10 d-block">Order Guard Status</small>
            <span id="order-delivery-success-badge" class="badge {{ $hasScore ? $badgeClass : 'bg-soft-secondary text-secondary' }} font-11">
                {{ $hasScore ? ((int) round($rate)).'%' : 'Syncing...' }}
            </span>
        </div>
        <button type="button" id="btn-refresh-fraud-score" class="btn btn-xs btn-outline-secondary p-1 ms-1 btn-refresh-order-guard" title="Re-check Courier Delivery Success Rate" data-order-id="{{ $orderId }}" data-current-score="{{ (int) $rate }}">
            <i class="fe-refresh-cw font-11"></i>
        </button>
    </div>

@elseif($mode === 'card')
    {{-- Mode 3: Detailed Intelligence Card (Circular Gauge + Courier Breakdown) --}}
    <div class="card shadow-sm mb-3 fraud-kpi-card order-guard-card-wrapper" id="fraud-checker-kpi-card" data-order-id="{{ $orderId }}">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <h5 class="card-title font-14 mb-0 text-dark d-flex align-items-center">
                <i class="fe-shield text-warning me-1.5 font-16"></i>
                <span>Order Guard: Courier Delivery History (Fraud Check)</span>
            </h5>
            <button type="button" id="btn-refresh-fraud-kpi" class="btn btn-xs btn-outline-secondary d-flex align-items-center gap-1 btn-refresh-order-guard" title="Re-check live courier delivery history from Hoorin API" data-order-id="{{ $orderId }}" data-current-score="{{ (int) $rate }}">
                <i class="fe-refresh-cw font-11"></i>
                <span class="font-11" id="fraud-kpi-btn-label">Re-check Stats</span>
            </button>
        </div>
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <!-- Left: Circular Success Ratio Gauge -->
                <div class="col-sm-5 col-md-4 text-center border-sm-end pe-sm-3">
                    <h6 class="text-muted text-uppercase font-12 fw-semibold mb-2" style="letter-spacing: 0.5px;">Success Ratio</h6>
                    <div class="circular-gauge-container">
                        <svg class="circular-gauge-svg" viewBox="0 0 100 100">
                            <circle class="circular-gauge-bg" cx="50" cy="50" r="45"></circle>
                            <circle class="circular-gauge-bar" id="fraud-gauge-bar" cx="50" cy="50" r="45"
                                    stroke="{{ $hasScore ? $circleColor : '#e2e8f0' }}"
                                    stroke-dasharray="282.743"
                                    stroke-dashoffset="{{ $dashOffset }}"></circle>
                        </svg>
                        <div class="circular-gauge-text">
                            <span id="fraud-kpi-rate" class="fs-2 fw-bold {{ $hasScore ? $textColor : 'text-muted' }}">
                                {{ $hasScore ? ((int) round($rate)).'%' : '--' }}
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 font-13 fw-semibold">
                        <span id="fraud-kpi-orders-count" class="text-primary"><span class="guard-all">{{ $total }}</span> Orders</span>
                        <span class="text-muted mx-1">|</span>
                        <span id="fraud-kpi-delivered-count" class="text-success"><span class="guard-dlvd">{{ $delivered }}</span> Delivered</span>
                    </div>
                    <div class="font-11 text-muted mt-1">
                        Total Return / Cancelled: <span class="text-danger fw-bold guard-cancl">{{ $canceled }}</span>
                    </div>
                </div>

                <!-- Right: Courier Breakdown Table -->
                <div class="col-sm-7 col-md-8 ps-sm-3">
                    <div class="table-responsive rounded border bg-light bg-opacity-50">
                        <table class="table table-sm table-borderless align-middle mb-0 font-12 fraud-breakdown-table">
                            <thead>
                                <tr>
                                    <th class="ps-2 text-start">Courier</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-center">Delivered</th>
                                    <th class="text-center pe-2">Return %</th>
                                </tr>
                            </thead>
                            <tbody id="fraud-courier-table-body">
                                @foreach($courierBreakdown as $courierName => $item)
                                    @php
                                        $cOrders = (int) ($item['orders'] ?? $item['Total Parcels'] ?? $item['Total Delivery'] ?? 0);
                                        $cDelivered = (int) ($item['delivered'] ?? $item['Delivered Parcels'] ?? $item['Successful Delivery'] ?? 0);
                                        $retRate = isset($item['return_rate']) ? (int) $item['return_rate'] : ($cOrders > 0 ? (int) round((($cOrders - $cDelivered) / $cOrders) * 100) : 0);
                                        $retClass = $retRate <= 15 ? 'text-success' : ($retRate <= 35 ? 'text-warning' : 'text-danger');
                                    @endphp
                                    <tr>
                                        <td class="ps-2 fw-semibold text-dark">{{ $courierName }}</td>
                                        <td class="text-center font-monospace">{{ $cOrders }}</td>
                                        <td class="text-center font-monospace">{{ $cDelivered }}</td>
                                        <td class="text-center pe-2 font-monospace fw-bold {{ $retClass }}">{{ $retRate }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
