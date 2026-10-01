<?php

namespace Modules\Shipping\Application\DTOs;

use Shared\Domain\ValueObjects\PhoneNumber;

class FraudScoreDTO
{
    public function __construct(
        public readonly PhoneNumber $phoneNumber,
        public readonly int $totalOrders,
        public readonly int $successfulOrders,
        public readonly int $cancelledOrders,
        public readonly float $successRatio,
        public readonly string $riskLevel, // 'low', 'medium', 'high'
        public readonly bool $isBlacklisted = false,
        public readonly array $details = []
    ) {}

    public function toArray(): array
    {
        return [
            'phone' => $this->phoneNumber->toE164(),
            'total_orders' => $this->totalOrders,
            'successful_orders' => $this->successfulOrders,
            'cancelled_orders' => $this->cancelledOrders,
            'success_ratio' => $this->successRatio,
            'risk_level' => $this->riskLevel,
            'is_blacklisted' => $this->isBlacklisted,
            'details' => $this->details,
        ];
    }
}