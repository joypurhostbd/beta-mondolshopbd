<?php

namespace Modules\Order\Domain\Services;

use Modules\Order\Application\DTOs\PriceBreakdownDTO;
use Modules\Order\Domain\Entities\CartEntity;
use Shared\Domain\ValueObjects\Discount;
use Shared\Domain\ValueObjects\Money;

class PricingEngine
{
    public function calculate(
        CartEntity $cart,
        ?Discount $discount = null,
        ?Money $shippingCharge = null,
        ?Money $tax = null
    ): PriceBreakdownDTO {
        return $this->calculateFromAmounts(
            $cart->getTotal(),
            $discount,
            $shippingCharge,
            $tax,
            $cart->getItemCount(),
            $cart->getTotalQuantity()
        );
    }

    public function calculateFromAmounts(
        Money $subtotal,
        ?Discount $discount = null,
        ?Money $shippingCharge = null,
        ?Money $tax = null,
        int $itemCount = 0,
        int $totalQuantity = 0
    ): PriceBreakdownDTO {
        $discountAmount = $discount ? $discount->calculate($subtotal) : Money::zero();
        $shipping = $shippingCharge ?? Money::zero();
        $taxAmount = $tax ?? Money::zero();

        // subtotal after discount cannot be less than zero
        $subtotalAfterDiscount = $subtotal->getAmount() >= $discountAmount->getAmount()
            ? $subtotal->subtract($discountAmount)
            : Money::zero();

        $grandTotal = $subtotalAfterDiscount->add($shipping)->add($taxAmount);

        return new PriceBreakdownDTO(
            subtotal: $subtotal->getAmount(),
            subtotalFormatted: $subtotal->format(),
            discountAmount: $discountAmount->getAmount(),
            discountAmountFormatted: $discountAmount->format(),
            shippingCharge: $shipping->getAmount(),
            shippingChargeFormatted: $shipping->format(),
            taxAmount: $taxAmount->getAmount(),
            taxAmountFormatted: $taxAmount->format(),
            grandTotal: $grandTotal->getAmount(),
            grandTotalFormatted: $grandTotal->format(),
            itemCount: $itemCount,
            totalQuantity: $totalQuantity
        );
    }
}