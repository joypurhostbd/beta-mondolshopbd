<?php

namespace Shared\Domain\Contracts\Modules;

use Shared\Domain\ValueObjects\Money;

interface ShippingModuleInterface
{
    /**
     * Calculate shipping charge based on district and cart subtotal.
     *
     * @param int|string $districtId
     * @param Money $subtotal
     * @return Money
     */
    public function calculateShippingCharge(int|string $districtId, Money $subtotal): Money;

    /**
     * Create shipment with courier.
     *
     * @param int|string $orderId
     * @param array $shippingDetails
     * @return array
     */
    public function createShipment(int|string $orderId, array $shippingDetails): array;
}