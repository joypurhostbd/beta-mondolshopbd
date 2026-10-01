<?php

namespace Modules\Order\Application\Actions;

use Modules\Order\Application\DTOs\PriceBreakdownDTO;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Services\PricingEngine;
use Shared\Domain\ValueObjects\Discount;
use Shared\Domain\ValueObjects\Money;

class CalculateOrderPriceAction
{
    public function __construct(
        private CartRepositoryInterface $cartRepository,
        private PricingEngine $pricingEngine
    ) {}

    public function execute(
        string $cartKey,
        ?string $discountType = null,
        ?float $discountValue = null,
        ?float $shippingCost = null,
        ?float $taxAmount = null
    ): PriceBreakdownDTO {
        $cart = $this->cartRepository->get($cartKey);

        $discount = null;
        if ($discountType === 'percentage' && $discountValue !== null && $discountValue > 0) {
            $discount = Discount::fromPercentage($discountValue);
        } elseif ($discountType === 'fixed' && $discountValue !== null && $discountValue > 0) {
            $discount = Discount::fromFixed(Money::from($discountValue));
        }

        $shipping = $shippingCost !== null && $shippingCost >= 0
            ? Money::from($shippingCost)
            : null;

        $tax = $taxAmount !== null && $taxAmount >= 0
            ? Money::from($taxAmount)
            : null;

        return $this->pricingEngine->calculate($cart, $discount, $shipping, $tax);
    }
}