<?php

namespace Modules\Payment\Application\DTOs;

use Shared\Application\DTO\DataTransferObject;

final class PaymentRedirectDTO extends DataTransferObject
{
    public function __construct(
        public readonly string $redirectUrl,
        public readonly string $gatewayTransactionId,
        public readonly string $method = 'GET',
        public readonly array $parameters = []
    ) {}
}