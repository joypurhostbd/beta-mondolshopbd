<?php

namespace Shared\Domain\Exceptions;

class PaymentFailedException extends DomainException
{
    public static function withReason(string $gateway, string $reason, ?string $transactionId = null): self
    {
        return new self(
            "Payment failed on gateway [{$gateway}]: {$reason}",
            ['gateway' => $gateway, 'reason' => $reason, 'transaction_id' => $transactionId],
            402
        );
    }
}