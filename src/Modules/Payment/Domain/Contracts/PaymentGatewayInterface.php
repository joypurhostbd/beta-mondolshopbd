<?php

namespace Modules\Payment\Domain\Contracts;

use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentRedirectDTO;
use Modules\Payment\Application\DTOs\PaymentResultDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Shared\Domain\Enums\PaymentMethodEnum;

interface PaymentGatewayInterface
{
    /**
     * Get payment method enum supported by this gateway.
     */
    public function getMethod(): PaymentMethodEnum;

    /**
     * Initiate payment transaction and return redirect instructions.
     */
    public function initiatePayment(PaymentInitiationDTO $dto): PaymentRedirectDTO;

    /**
     * Verify payment status from gateway callback/IPN payload.
     */
    public function verifyPayment(PaymentVerificationDTO $dto): PaymentResultDTO;
}