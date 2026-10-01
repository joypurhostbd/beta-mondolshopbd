/**
 * Centralized Order Guard Real-time Intelligence Engine
 * Synchronizes all Order Guard widgets (List Column, Top Badge, Detail KPI Card)
 */
(function (window, $) {
    'use strict';

    var OrderGuard = {
        refresh: function (orderId, options) {
            options = options || {};
            if (!orderId) return;

            var $buttons = $('[data-order-id="' + orderId + '"]').filter('.btn-refresh-order-guard, #btn-refresh-fraud-score, #btn-refresh-fraud-kpi')
                .add('.btn-refresh-order-guard[data-order-id="' + orderId + '"]');

            $buttons.prop('disabled', true);
            $buttons.find('i').addClass('fa-spin');

            var csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() || (typeof window.csrf_token !== 'undefined' ? window.csrf_token : '');

            $.ajax({
                type: 'POST',
                url: '/admin/order/refresh-fraud/' + orderId,
                data: {
                    _token: csrfToken,
                    force: options.force !== undefined ? (options.force ? 1 : 0) : 1
                },
                dataType: 'json',
                success: function (res) {
                    $buttons.prop('disabled', false);
                    $buttons.find('i').removeClass('fa-spin');

                    if (res && res.status === 'success') {
                        OrderGuard.applyUpdate(orderId, res);
                        
                        // Dispatch global event for custom external listeners
                        var event = new CustomEvent('order-guard:updated', { detail: { orderId: orderId, data: res } });
                        window.dispatchEvent(event);

                        if (!options.silent && typeof toastr !== 'undefined') {
                            toastr.success(res.message || 'Order Guard Report updated successfully');
                        }
                        if (typeof options.success === 'function') options.success(res);
                    } else {
                        if (!options.silent && typeof toastr !== 'undefined') {
                            toastr.error(res.message || 'Could not update Order Guard Report');
                        }
                        if (typeof options.error === 'function') options.error(res);
                    }
                },
                error: function (xhr) {
                    $buttons.prop('disabled', false);
                    $buttons.find('i').removeClass('fa-spin');

                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to refresh Order Guard Report.';
                    if (!options.silent && typeof toastr !== 'undefined') {
                        toastr.error(msg);
                    }
                    if (typeof options.error === 'function') options.error(xhr);
                }
            });
        },

        applyUpdate: function (orderId, data) {
            var rate = parseFloat(data.delivery_rate || 0);
            var formattedRate = data.formatted_rate || (Math.round(rate) + '%');
            var total = parseInt(data.total_orders || (data.stats && data.stats.total_parcel) || 0);
            var delivered = parseInt(data.total_delivered || (data.stats && data.stats.total_delivered) || 0);
            var canceled = parseInt(data.total_cancel || (data.stats && data.stats.total_cancel) || 0);
            var badgeClass = data.badge_class || (rate >= 80 ? 'bg-success' : (rate >= 50 ? 'bg-warning' : 'bg-danger'));
            var textColor = rate >= 80 ? 'text-success' : (rate >= 50 ? 'text-warning' : 'text-danger');
            var strokeColor = rate >= 80 ? '#10b981' : (rate >= 50 ? '#f59e0b' : '#ef4444');

            // 1. Update Compact View (Order list / DataTable row)
            var $box = $('#order-guard-' + orderId);
            if ($box.length) {
                $box.find('.order-guard-progress-bar').css('width', rate + '%').attr('aria-valuenow', rate);
                $box.find('.guard-all').text(total);
                $box.find('.guard-dlvd').text(delivered);
                $box.find('.guard-cancl').text(canceled);
            }

            // 2. Update Header Badge View (Order Edit top)
            var $badge = $('#order-delivery-success-badge');
            if ($badge.length) {
                $badge.removeClass('bg-success bg-warning bg-danger bg-soft-secondary text-secondary')
                    .addClass(badgeClass)
                    .text(formattedRate);
            }

            // 3. Update Detailed Intelligence Card (Order Edit card)
            var $card = $('#fraud-checker-kpi-card');
            if ($card.length) {
                var $gaugeBar = $('#fraud-gauge-bar');
                var $kpiRate = $('#fraud-kpi-rate');
                var $ordersCount = $('#fraud-kpi-orders-count');
                var $deliveredCount = $('#fraud-kpi-delivered-count');

                if ($gaugeBar.length) {
                    var circumference = 282.743;
                    var offset = circumference - (circumference * (Math.min(100, Math.max(0, rate)) / 100));
                    $gaugeBar.attr('stroke', strokeColor).css('stroke-dashoffset', offset);
                }

                if ($kpiRate.length) {
                    $kpiRate.removeClass('text-success text-warning text-danger text-muted')
                        .addClass(textColor)
                        .text(formattedRate);
                }

                if ($ordersCount.length) {
                    $ordersCount.html('<span class="guard-all">' + total + '</span> Orders');
                }
                if ($deliveredCount.length) {
                    $deliveredCount.html('<span class="guard-dlvd">' + delivered + '</span> Delivered');
                }
                $card.find('.guard-cancl').text(canceled);

                // Render courier breakdown table if available
                var breakdown = data.courier_breakdown || {};
                var $tableBody = $('#fraud-courier-table-body');
                if ($tableBody.length && Object.keys(breakdown).length > 0) {
                    var rowsHtml = '';
                    $.each(breakdown, function (courierName, item) {
                        var cOrders = parseInt(item.orders || item['Total Parcels'] || item['Total Delivery'] || 0);
                        var cDelivered = parseInt(item.delivered || item['Delivered Parcels'] || item['Successful Delivery'] || 0);
                        var retRate = item.return_rate !== undefined ? parseInt(item.return_rate) : (cOrders > 0 ? Math.round(((cOrders - cDelivered) / cOrders) * 100) : 0);
                        var retClass = retRate <= 15 ? 'text-success' : (retRate <= 35 ? 'text-warning' : 'text-danger');

                        rowsHtml += '<tr>' +
                            '<td class="ps-2 fw-semibold text-dark">' + courierName + '</td>' +
                            '<td class="text-center font-monospace">' + cOrders + '</td>' +
                            '<td class="text-center font-monospace">' + cDelivered + '</td>' +
                            '<td class="text-center pe-2 font-monospace fw-bold ' + retClass + '">' + retRate + '%</td>' +
                            '</tr>';
                    });
                    $tableBody.html(rowsHtml);
                }
            }
        }
    };

    // Global Delegated Click Listener for All Order Guard Buttons
    $(document).on('click', '.btn-refresh-order-guard, #btn-refresh-fraud-score, #btn-refresh-fraud-kpi', function (e) {
        e.preventDefault();
        var orderId = $(this).data('order-id');
        if (orderId) {
            OrderGuard.refresh(orderId, { force: true });
        }
    });

    window.OrderGuard = OrderGuard;
})(window, jQuery);
