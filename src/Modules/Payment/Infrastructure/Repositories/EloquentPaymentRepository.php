<?php

namespace Modules\Payment\Infrastructure\Repositories;

use App\Models\Payment;
use Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use Modules\Payment\Domain\Entities\PaymentEntity;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;

class EloquentPaymentRepository implements PaymentRepositoryInterface
{
    public function save(PaymentEntity $payment): PaymentEntity
    {
        $model = $payment->getId() ? Payment::find($payment->getId()) : new Payment();
        if (!$model) {
            $model = new Payment();
        }

        $model->order_id = $payment->getOrderId();
        $model->customer_id = $payment->getCustomerId() ?? 0;
        $model->payment_method = $payment->getPaymentMethod()->value;
        $model->amount = $payment->getAmount()->getAmount();
        $model->payment_status = $payment->getPaymentStatus()->value;
        $model->trx_id = $payment->getTransactionId();
        $model->save();

        return new PaymentEntity(
            id: $model->id,
            orderId: $model->order_id,
            customerId: $model->customer_id ? (int) $model->customer_id : null,
            paymentMethod: PaymentMethodEnum::tryFrom($model->payment_method) ?? PaymentMethodEnum::COD,
            amount: Money::from((float) $model->amount),
            paymentStatus: PaymentStatusEnum::tryFrom($model->payment_status) ?? PaymentStatusEnum::PENDING,
            transactionId: $model->trx_id,
            bankTransactionId: null,
            gatewayResponse: []
        );
    }

    public function findByOrderId(int|string $orderId): ?PaymentEntity
    {
        $model = Payment::where('order_id', $orderId)->first();
        return $model ? $this->toEntity($model) : null;
    }

    public function findByTransactionId(string $transactionId): ?PaymentEntity
    {
        $model = Payment::where('trx_id', $transactionId)->first();
        return $model ? $this->toEntity($model) : null;
    }

    private function toEntity(Payment $model): PaymentEntity
    {
        return new PaymentEntity(
            id: $model->id,
            orderId: $model->order_id,
            customerId: $model->customer_id ? (int) $model->customer_id : null,
            paymentMethod: PaymentMethodEnum::tryFrom($model->payment_method) ?? PaymentMethodEnum::COD,
            amount: Money::from((float) $model->amount),
            paymentStatus: PaymentStatusEnum::tryFrom($model->payment_status) ?? PaymentStatusEnum::PENDING,
            transactionId: $model->trx_id,
            bankTransactionId: null,
            gatewayResponse: []
        );
    }
}