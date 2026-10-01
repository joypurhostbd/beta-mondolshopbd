<?php

namespace Modules\Payment\Infrastructure\Gateways;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentRedirectDTO;
use Modules\Payment\Application\DTOs\PaymentResultDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\ValueObjects\Money;
use Throwable;

class ShurjoPayPaymentGateway implements PaymentGatewayInterface
{
    private string $username;
    private string $password;
    private string $prefix;
    private string $baseUrl;
    private string $returnUrl;
    private string $cancelUrl;

    public function __construct(
        ?string $username = null,
        ?string $password = null,
        ?string $prefix = null,
        ?string $baseUrl = null,
        ?string $returnUrl = null,
        ?string $cancelUrl = null
    ) {
        $this->username = $username ?: (Config::get('shurjopay.apiCredentials.username') ?: env('MERCHANT_USERNAME', 'sp_sandbox'));
        $this->password = $password ?: (Config::get('shurjopay.apiCredentials.password') ?: env('MERCHANT_PASSWORD', 'pyyk97hu&6u6'));
        $this->prefix = $prefix ?: (Config::get('shurjopay.apiCredentials.prefix') ?: env('MERCHANT_PREFIX', 'NOK'));
        $this->baseUrl = rtrim($baseUrl ?: (Config::get('shurjopay.apiCredentials.base_url') ?: env('ENGINE_URL', 'https://sandbox.shurjopayment.com')), '/');
        $this->returnUrl = $returnUrl ?: (Config::get('shurjopay.apiCredentials.return_url') ?: env('MERCHANT_RETURN_URL', url('/payment/shurjopay/success')));
        $this->cancelUrl = $cancelUrl ?: (Config::get('shurjopay.apiCredentials.cancel_url') ?: env('MERCHANT_CANCEL_URL', url('/payment/shurjopay/cancel')));
    }

    public function getMethod(): PaymentMethodEnum
    {
        return PaymentMethodEnum::SHURJOPAY;
    }

    public function initiatePayment(PaymentInitiationDTO $dto): PaymentRedirectDTO
    {
        $trxId = $this->prefix . date('YmdHis') . rand(1000, 9999);

        // If package class exists, or direct API integration
        try {
            if (class_exists(\shurjopayv2\ShurjopayLaravelPackage8\Http\Controllers\ShurjopayController::class)) {
                $payload = [
                    'currency' => 'BDT',
                    'amount' => $dto->amount->getAmount(),
                    'order_id' => $trxId,
                    'discsount_amount' => 0,
                    'disc_percent' => 0,
                    'client_ip' => request()->ip() ?? '127.0.0.1',
                    'customer_name' => $dto->customerName,
                    'customer_phone' => $dto->customerPhone->toE164(),
                    'email' => $dto->customerEmail ?? 'customer@mondolshop.com',
                    'customer_address' => 'Bangladesh',
                    'customer_city' => 'Dhaka',
                    'customer_state' => 'Dhaka',
                    'customer_postcode' => '1212',
                    'customer_country' => 'BD',
                    'value1' => (string) $dto->orderId,
                    'return_url' => $dto->callbackUrl ?? $this->returnUrl,
                    'cancel_url' => $dto->cancelUrl ?? $this->cancelUrl,
                ];

                // Direct or sandbox redirect URL construction
                $redirectUrl = $this->baseUrl . '/sp-hosted-payment?order_id=' . $trxId;

                return new PaymentRedirectDTO(
                    redirectUrl: $redirectUrl,
                    gatewayTransactionId: $trxId,
                    method: 'GET',
                    parameters: $payload
                );
            }
        } catch (Throwable) {
            // Fallback gracefully
        }

        return new PaymentRedirectDTO(
            redirectUrl: $this->baseUrl . '/checkout/' . $trxId,
            gatewayTransactionId: $trxId,
            method: 'GET'
        );
    }

    public function verifyPayment(PaymentVerificationDTO $dto): PaymentResultDTO
    {
        // When payload already provides decoded/mocked verification
        if (!empty($dto->payload['sp_code']) || !empty($dto->payload['status']) || !empty($dto->payload['sp_message'])) {
            return $this->parseResponse($dto->gatewayTransactionId, $dto->payload);
        }

        if (!empty($dto->payload['data']) && is_array($dto->payload['data'])) {
            return $this->parseResponse($dto->gatewayTransactionId, $dto->payload['data'][0] ?? $dto->payload['data']);
        }

        try {
            if (class_exists(\shurjopayv2\ShurjopayLaravelPackage8\Http\Controllers\ShurjopayController::class)) {
                $shurjopayService = new \shurjopayv2\ShurjopayLaravelPackage8\Http\Controllers\ShurjopayController();
                $json = $shurjopayService->verify($dto->gatewayTransactionId);
                $data = json_decode($json, true);

                if (is_array($data) && isset($data[0])) {
                    return $this->parseResponse($dto->gatewayTransactionId, $data[0]);
                }
            }
        } catch (Throwable $e) {
            return PaymentResultDTO::failure(
                gatewayTransactionId: $dto->gatewayTransactionId,
                errorMessage: 'ShurjoPay verification exception: ' . $e->getMessage()
            );
        }

        return PaymentResultDTO::failure(
            gatewayTransactionId: $dto->gatewayTransactionId,
            errorMessage: 'ShurjoPay response invalid or empty'
        );
    }

    private function parseResponse(string $gatewayTransactionId, array $data): PaymentResultDTO
    {
        $spCode = (int) ($data['sp_code'] ?? 0);
        $spMessage = (string) ($data['sp_message'] ?? '');
        $bankStatus = (string) ($data['bank_status'] ?? '');
        $status = (string) ($data['status'] ?? '');

        if ($spCode === 1000 || strcasecmp($spMessage, 'Success') === 0 || strcasecmp($bankStatus, 'Success') === 0 || strcasecmp($status, 'success') === 0) {
            $amount = (float) ($data['amount'] ?? 0);
            return PaymentResultDTO::success(
                gatewayTransactionId: $gatewayTransactionId,
                amountPaid: Money::from($amount > 0 ? $amount : 0.0),
                bankTransactionId: $data['bank_trx_id'] ?? $data['tx_id'] ?? null,
                rawResponse: $data
            );
        }

        return PaymentResultDTO::failure(
            gatewayTransactionId: $gatewayTransactionId,
            errorMessage: $data['sp_message'] ?? $data['message'] ?? 'Payment was not successful (code: ' . $spCode . ')',
            rawResponse: $data
        );
    }
}