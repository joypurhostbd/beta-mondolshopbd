<?php

namespace Shared\Domain\Contracts\Modules;

use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\ValueObjects\Money;

interface PaymentModuleInterface
{
    /**
     * Process payment for an order.
     *
     * @param int|string $orderId
     * @param Money $amount
     * @param PaymentMethodEnum $method
     * @return array
     */
    public function processPayment(int|string $orderId, Money $amount, PaymentMethodEnum $method): array;

    /**
     * Verify payment transaction status.
     *
     * @param string $transactionId
     * @param array $payload
     * @return bool
     */
    public function verifyPayment(string $transactionId, array $payload = []): bool;
}