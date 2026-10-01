<?php

namespace Modules\Payment\Application\Actions;

use Illuminate\Support\Facades\Event;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Application\Services\PaymentGatewayManager;
use Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use Modules\Payment\Domain\Events\PaymentProcessedEvent;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Infrastructure\Services\IdempotencyService;

class ProcessPaymentWebhookAction
{
    public function __construct(
        private PaymentGatewayManager $gatewayManager,
        private PaymentRepositoryInterface $paymentRepository,
        private IdempotencyService $idempotencyService
    ) {}

    public function execute(PaymentMethodEnum $method, string $transactionId, array $payload, ?string $customIdempotencyKey = null): array
    {
        $idempotencyKey = $customIdempotencyKey ?: sprintf('payment_webhook_%s_%s', $method->value, $transactionId);
        $requestPath = '/api/webhooks/payment/' . $method->value;

        return $this->idempotencyService->execute(
            key: $idempotencyKey,
            path: $requestPath,
            payload: $payload,
            callback: function () use ($method, $transactionId, $payload) {
                $gateway = $this->gatewayManager->resolve($method);
                $payment = $this->paymentRepository->findByTransactionId($transactionId);

                $orderId = $payment ? $payment->getOrderId() : ($payload['order_id'] ?? $payload['orderId'] ?? null);

                $verifyDTO = new PaymentVerificationDTO(
                    gatewayTransactionId: $transactionId,
                    orderId: $orderId,
                    payload: $payload
                );

                $result = $gateway->verifyPayment($verifyDTO);

                if ($payment) {
                    if ($result->isSuccessful) {
                        $payment->markAsPaid($transactionId, $result->bankTransactionId, $result->rawResponse);
                    } else {
                        $payment->markAsFailed($result->errorMessage ?? 'Payment failed', $result->rawResponse);
                    }
                    $this->paymentRepository->save($payment);
                }

                if ($orderId) {
                    Event::dispatch(new PaymentProcessedEvent(
                        orderId: $orderId,
                        method: $method,
                        amount: $result->amountPaid,
                        status: $result->status,
                        transactionId: $transactionId,
                        errorMessage: $result->errorMessage
                    ));
                }

                return [
                    'success' => $result->isSuccessful,
                    'status' => $result->status->value,
                    'gateway_transaction_id' => $result->gatewayTransactionId,
                    'bank_transaction_id' => $result->bankTransactionId,
                    'error_message' => $result->errorMessage,
                ];
            }
        );
    }
}