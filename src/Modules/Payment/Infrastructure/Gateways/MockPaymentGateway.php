<?php

namespace Modules\Payment\Infrastructure\Gateways;

use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentRedirectDTO;
use Modules\Payment\Application\DTOs\PaymentResultDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use Shared\Domain\Enums\PaymentMethodEnum;

class MockPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private PaymentMethodEnum $method = PaymentMethodEnum::SHURJOPAY
    ) {}

    public function getMethod(): PaymentMethodEnum
    {
        return $this->method;
    }

    public function initiatePayment(PaymentInitiationDTO $dto): PaymentRedirectDTO
    {
        $mockTrx = 'MOCK-TRX-' . strtoupper(substr(uniqid(), -6));
        return new PaymentRedirectDTO(
            redirectUrl: 'https://sandbox.payment.gateway/checkout/' . $mockTrx,
            gatewayTransactionId: $mockTrx,
            method: 'GET'
        );
    }

    public function verifyPayment(PaymentVerificationDTO $dto): PaymentResultDTO
    {
        $status = $dto->payload['status'] ?? 'success';

        if ($status === 'success') {
            return PaymentResultDTO::success(
                gatewayTransactionId: $dto->gatewayTransactionId,
                amountPaid: \Shared\Domain\ValueObjects\Money::from(1000.0),
                bankTransactionId: 'BANK-' . rand(10000, 99999),
                rawResponse: ['status' => 'success', 'message' => 'Payment verified successfully']
            );
        }

        return PaymentResultDTO::failure(
            gatewayTransactionId: $dto->gatewayTransactionId,
            errorMessage: 'Payment declined by gateway',
            rawResponse: ['status' => 'failed']
        );
    }
}