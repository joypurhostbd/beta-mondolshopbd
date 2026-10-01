<?php

namespace Modules\Payment\Domain\Contracts;

use Modules\Payment\Domain\Entities\PaymentEntity;

interface PaymentRepositoryInterface
{
    public function save(PaymentEntity $payment): PaymentEntity;

    public function findByOrderId(int|string $orderId): ?PaymentEntity;

    public function findByTransactionId(string $transactionId): ?PaymentEntity;
}