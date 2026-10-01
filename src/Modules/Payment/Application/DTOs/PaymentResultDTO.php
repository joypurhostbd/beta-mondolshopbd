<?php

namespace Modules\Payment\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;

final class PaymentResultDTO extends DataTransferObject
{
    public function __construct(
        public readonly bool $isSuccessful,
        public readonly PaymentStatusEnum $status,
        public readonly string $gatewayTransactionId,
        public readonly ?string $bankTransactionId = null,
        public readonly ?Money $amountPaid = null,
        public readonly ?string $currency = 'BDT',
        public readonly ?string $errorMessage = null,
        public readonly array $rawResponse = []
    ) {}

    public static function success(
        string $gatewayTransactionId,
        Money $amountPaid,
        ?string $bankTransactionId = null,
        array $rawResponse = []
    ): self {
        return new self(
            isSuccessful: true,
            status: PaymentStatusEnum::PAID,
            gatewayTransactionId: $gatewayTransactionId,
            bankTransactionId: $bankTransactionId,
            amountPaid: $amountPaid,
            rawResponse: $rawResponse
        );
    }

    public static function failure(
        string $gatewayTransactionId,
        string $errorMessage,
        array $rawResponse = []
    ): self {
        return new self(
            isSuccessful: false,
            status: PaymentStatusEnum::FAILED,
            gatewayTransactionId: $gatewayTransactionId,
            errorMessage: $errorMessage,
            rawResponse: $rawResponse
        );
    }
}