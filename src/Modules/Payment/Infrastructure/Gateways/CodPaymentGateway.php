<?php

namespace Modules\Payment\Infrastructure\Gateways;

use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentRedirectDTO;
use Modules\Payment\Application\DTOs\PaymentResultDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use Shared\Domain\Enums\PaymentMethodEnum;

class CodPaymentGateway implements PaymentGatewayInterface
{
    public function getMethod(): PaymentMethodEnum
    {
        return PaymentMethodEnum::COD;
    }

    public function initiatePayment(PaymentInitiationDTO $dto): PaymentRedirectDTO
    {
        return new PaymentRedirectDTO(
            redirectUrl: $dto->callbackUrl ?? '/order/success/' . $dto->orderId,
            gatewayTransactionId: 'COD-' . $dto->invoiceId,
            method: 'GET'
        );
    }

    public function verifyPayment(PaymentVerificationDTO $dto): PaymentResultDTO
    {
        return PaymentResultDTO::success(
            gatewayTransactionId: $dto->gatewayTransactionId,
            amountPaid: \Shared\Domain\ValueObjects\Money::zero(),
            bankTransactionId: null,
            rawResponse: ['method' => 'COD', 'status' => 'pending_on_delivery']
        );
    }
}