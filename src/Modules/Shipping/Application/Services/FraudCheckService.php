<?php

namespace Modules\Shipping\Application\Services;

use Modules\Shipping\Application\DTOs\FraudScoreDTO;
use Modules\Shipping\Domain\Contracts\FraudCheckerInterface;
use Shared\Domain\ValueObjects\PhoneNumber;

class FraudCheckService
{
    /** @var FraudCheckerInterface[] */
    private array $checkers;

    public function __construct(array $checkers = [])
    {
        $this->checkers = $checkers;
    }

    public function addChecker(FraudCheckerInterface $checker): void
    {
        $this->checkers[] = $checker;
    }

    public function evaluate(PhoneNumber|string $phone): FraudScoreDTO
    {
        $phoneNumber = is_string($phone) ? PhoneNumber::fromString($phone) : $phone;

        $highestRisk = 'low';
        $isBlacklisted = false;
        $totalOrders = 0;
        $successfulOrders = 0;
        $cancelledOrders = 0;
        $allDetails = [];

        foreach ($this->checkers as $checker) {
            $score = $checker->checkCustomer($phoneNumber);
            $totalOrders += $score->totalOrders;
            $successfulOrders += $score->successfulOrders;
            $cancelledOrders += $score->cancelledOrders;
            $allDetails[] = $score->details;

            if ($score->isBlacklisted) {
                $isBlacklisted = true;
            }

            if ($score->riskLevel === 'high') {
                $highestRisk = 'high';
            } else if ($score->riskLevel === 'medium' && $highestRisk !== 'high') {
                $highestRisk = 'medium';
            }
        }

        $overallRatio = $totalOrders > 0 ? round(($successfulOrders / $totalOrders) * 100, 2) : 100.0;

        return new FraudScoreDTO(
            phoneNumber: $phoneNumber,
            totalOrders: $totalOrders,
            successfulOrders: $successfulOrders,
            cancelledOrders: $cancelledOrders,
            successRatio: $overallRatio,
            riskLevel: $isBlacklisted ? 'high' : $highestRisk,
            isBlacklisted: $isBlacklisted,
            details: $allDetails
        );
    }
}