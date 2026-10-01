<?php

namespace Modules\Payment\Application\Services;

use Illuminate\Support\Facades\Event;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use Modules\Payment\Domain\Events\PaymentProcessedEvent;
use Shared\Domain\Contracts\Modules\PaymentModuleInterface;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;

class PaymentService implements PaymentModuleInterface
{
    public function __construct(
        private PaymentGatewayManager $gatewayManager,
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    public function processPayment(int|string $orderId, Money $amount, PaymentMethodEnum $method): array
    {
        $gateway = $this->gatewayManager->resolve($method);

        // Find or create payment entity
        $payment = $this->paymentRepository->findByOrderId($orderId);
        if (!$payment) {
            $payment = new \Modules\Payment\Domain\Entities\PaymentEntity(
                id: null,
                orderId: $orderId,
                customerId: null,
                paymentMethod: $method,
                amount: $amount,
                paymentStatus: PaymentStatusEnum::PENDING
            );
            $payment = $this->paymentRepository->save($payment);
        }

        return $payment->toArray();
    }

    public function verifyPayment(string $transactionId, array $payload = []): bool
    {
        $payment = $this->paymentRepository->findByTransactionId($transactionId);
        if (!$payment) {
            return false;
        }

        $gateway = $this->gatewayManager->resolve($payment->getPaymentMethod());
        $result = $gateway->verifyPayment(new PaymentVerificationDTO($transactionId, $payment->getOrderId(), $payload));

        if ($result->isSuccessful) {
            $payment->markAsPaid($transactionId, $result->bankTransactionId, $result->rawResponse);
            $this->paymentRepository->save($payment);

            Event::dispatch(new PaymentProcessedEvent(
                orderId: $payment->getOrderId(),
                method: $payment->getPaymentMethod(),
                amount: $payment->getAmount(),
                status: PaymentStatusEnum::PAID,
                transactionId: $transactionId
            ));

            return true;
        }

        $payment->markAsFailed($result->errorMessage ?? 'Verification failed', $result->rawResponse);
        $this->paymentRepository->save($payment);

        Event::dispatch(new PaymentProcessedEvent(
            orderId: $payment->getOrderId(),
            method: $payment->getPaymentMethod(),
            amount: $payment->getAmount(),
            status: PaymentStatusEnum::FAILED,
            transactionId: $transactionId,
            errorMessage: $result->errorMessage
        ));

        return false;
    }
}