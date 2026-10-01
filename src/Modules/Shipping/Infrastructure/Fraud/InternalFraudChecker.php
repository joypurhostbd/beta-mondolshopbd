<?php

namespace Modules\Shipping\Infrastructure\Fraud;

use App\Models\Customer;
use App\Models\IpBlock;
use App\Models\Order;
use App\Models\Shipping;
use Modules\Shipping\Application\DTOs\FraudScoreDTO;
use Modules\Shipping\Domain\Contracts\FraudCheckerInterface;
use Shared\Domain\ValueObjects\PhoneNumber;
use Throwable;

class InternalFraudChecker implements FraudCheckerInterface
{
    public function checkCustomer(PhoneNumber $phoneNumber): FraudScoreDTO
    {
        $rawPhone = $phoneNumber->getValue();
        $e164Phone = $phoneNumber->toE164();

        // Check if phone or IP is blocked
        $isBlacklisted = false;
        try {
            $isBlacklisted = IpBlock::where('ip_no', $rawPhone)
                ->orWhere('ip_no', $e164Phone)
                ->exists();
        } catch (Throwable) {}

        // Query orders by phone in shipping or customer table
        $totalOrders = 0;
        $successfulOrders = 0;
        $cancelledOrders = 0;

        try {
            $shippingIds = Shipping::where('phone', $rawPhone)
                ->orWhere('phone', $e164Phone)
                ->pluck('id')
                ->toArray();

            $customerIds = Customer::where('phone', $rawPhone)
                ->orWhere('phone', $e164Phone)
                ->pluck('id')
                ->toArray();

            $query = Order::query();
            if (!empty($shippingIds) && !empty($customerIds)) {
                $query->where(function ($q) use ($shippingIds, $customerIds) {
                    $q->whereIn('shipping_id', $shippingIds)
                      ->orWhereIn('customer_id', $customerIds);
                });
            } elseif (!empty($shippingIds)) {
                $query->whereIn('shipping_id', $shippingIds);
            } elseif (!empty($customerIds)) {
                $query->whereIn('customer_id', $customerIds);
            } else {
                $query->whereRaw('1 = 0');
            }

            $orders = $query->get();

            $totalOrders = $orders->count();
            foreach ($orders as $order) {
                $status = strtolower((string) ($order->order_status ?? $order->status ?? ''));
                if (in_array($status, ['completed', 'delivered', '4', '7'])) {
                    $successfulOrders++;
                } else if (in_array($status, ['cancelled', 'canceled', 'returned', '5', '6', '8', '9'])) {
                    $cancelledOrders++;
                }
            }
        } catch (Throwable) {}

        $ratio = $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100, 2) : 100.0;

        $riskLevel = 'low';
        if ($isBlacklisted || ($totalOrders >= 3 && $ratio < 40.0) || ($cancelledOrders >= 3 && $successfulOrders === 0)) {
            $riskLevel = 'high';
        } else if ($totalOrders >= 2 && $ratio < 70.0) {
            $riskLevel = 'medium';
        }

        return new FraudScoreDTO(
            phoneNumber: $phoneNumber,
            totalOrders: $totalOrders,
            successfulOrders: $successfulOrders,
            cancelledOrders: $cancelledOrders,
            successRatio: $ratio,
            riskLevel: $riskLevel,
            isBlacklisted: $isBlacklisted,
            details: ['source' => 'internal_history']
        );
    }
}