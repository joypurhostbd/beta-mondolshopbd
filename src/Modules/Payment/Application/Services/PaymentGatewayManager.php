<?php

namespace Modules\Payment\Application\Services;

use InvalidArgumentException;
use Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use Shared\Domain\Enums\PaymentMethodEnum;

class PaymentGatewayManager
{
    /** @var array<string, PaymentGatewayInterface> */
    private array $gateways = [];

    public function register(PaymentGatewayInterface $gateway): self
    {
        $this->gateways[$gateway->getMethod()->value] = $gateway;
        return $this;
    }

    public function resolve(PaymentMethodEnum|string $method): PaymentGatewayInterface
    {
        $key = $method instanceof PaymentMethodEnum ? $method->value : $method;

        if (!isset($this->gateways[$key])) {
            throw new InvalidArgumentException("No payment gateway registered for method [{$key}].");
        }

        return $this->gateways[$key];
    }

    public function has(PaymentMethodEnum|string $method): bool
    {
        $key = $method instanceof PaymentMethodEnum ? $method->value : $method;
        return isset($this->gateways[$key]);
    }
}