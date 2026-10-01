<?php

namespace Modules\Shipping\Application\Services;

use Modules\Shipping\Domain\Contracts\CourierGatewayInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;

class CourierGatewayManager
{
    /** @var array<string, CourierGatewayInterface> */
    private array $gateways = [];

    public function register(CourierGatewayInterface $gateway): void
    {
        $this->gateways[strtolower($gateway->getCourierName())] = $gateway;
    }

    public function resolve(string $courierName): CourierGatewayInterface
    {
        $key = strtolower($courierName);
        if (!isset($this->gateways[$key])) {
            throw new EntityNotFoundException("Courier gateway [{$courierName}] is not registered.");
        }
        return $this->gateways[$key];
    }

    public function has(string $courierName): bool
    {
        return isset($this->gateways[strtolower($courierName)]);
    }

    public function all(): array
    {
        return $this->gateways;
    }
}