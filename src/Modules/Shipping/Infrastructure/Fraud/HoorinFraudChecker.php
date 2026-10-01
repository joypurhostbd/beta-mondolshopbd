<?php

namespace Modules\Shipping\Infrastructure\Fraud;

use Illuminate\Support\Facades\Http;
use Modules\Shipping\Application\DTOs\FraudScoreDTO;
use Modules\Shipping\Domain\Contracts\FraudCheckerInterface;
use Shared\Domain\ValueObjects\PhoneNumber;
use Throwable;

class HoorinFraudChecker implements FraudCheckerInterface
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct(?string $apiUrl = null, ?string $apiKey = null)
    {
        $this->apiUrl = rtrim($apiUrl ?: env('HOORIN_API_URL', 'https://api.hoorin.com/v1/fraud-check'), '/');
        $this->apiKey = $apiKey ?: env('HOORIN_API_KEY', 'sandbox_hoorin_key');
    }

    public function checkCustomer(PhoneNumber $phoneNumber): FraudScoreDTO
    {
        try {
            $response = Http::timeout(3)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'phone' => $phoneNumber->getValue(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $total = (int) ($data['total_parcels'] ?? 0);
                $delivered = (int) ($data['delivered_parcels'] ?? 0);
                $cancelled = (int) ($data['cancelled_parcels'] ?? 0);
                $ratio = $total > 0 ? round(($delivered / $total) * 100, 2) : 100.0;

                $risk = $ratio >= 75.0 ? 'low' : ($ratio >= 50.0 ? 'medium' : 'high');

                return new FraudScoreDTO(
                    phoneNumber: $phoneNumber,
                    totalOrders: $total,
                    successfulOrders: $delivered,
                    cancelledOrders: $cancelled,
                    successRatio: $ratio,
                    riskLevel: $risk,
                    isBlacklisted: (bool) ($data['is_fraud'] ?? false),
                    details: $data
                );
            }
        } catch (Throwable) {}

        // Safe fallback when offline / sandbox
        return new FraudScoreDTO(
            phoneNumber: $phoneNumber,
            totalOrders: 0,
            successfulOrders: 0,
            cancelledOrders: 0,
            successRatio: 100.0,
            riskLevel: 'low',
            isBlacklisted: false,
            details: ['source' => 'hoorin_sandbox']
        );
    }
}