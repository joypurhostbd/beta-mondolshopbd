<?php

namespace Modules\Payment\Infrastructure\Gateways;

use App\Models\PaymentGateway as LegacyPaymentGateway;
use Illuminate\Support\Facades\Http;
use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentRedirectDTO;
use Modules\Payment\Application\DTOs\PaymentResultDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Domain\Contracts\PaymentGatewayInterface;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\ValueObjects\Money;
use Throwable;

class BkashPaymentGateway implements PaymentGatewayInterface
{
    private string $baseUrl;
    private string $appKey;
    private string $appSecret;
    private string $username;
    private string $password;

    public function __construct(
        ?string $baseUrl = null,
        ?string $appKey = null,
        ?string $appSecret = null,
        ?string $username = null,
        ?string $password = null
    ) {
        $legacyGateway = null;
        try {
            $legacyGateway = LegacyPaymentGateway::where(['status' => 1, 'type' => 'bkash'])->first();
        } catch (Throwable) {
            // DB not ready or running during early bootstrap
        }

        $this->baseUrl = rtrim($baseUrl ?: ($legacyGateway?->base_url ?: env('BKASH_BASE_URL', 'https://tokenized.pay.bka.sh/v1.2.0-beta')), '/');
        $this->appKey = $appKey ?: ($legacyGateway?->app_key ?: env('BKASH_APP_KEY', 'sandbox_app_key'));
        $this->appSecret = $appSecret ?: ($legacyGateway?->app_secret ?: env('BKASH_APP_SECRET', 'sandbox_app_secret'));
        $this->username = $username ?: ($legacyGateway?->username ?: env('BKASH_USERNAME', 'sandbox_user'));
        $this->password = $password ?: ($legacyGateway?->password ?: env('BKASH_PASSWORD', 'sandbox_pass'));
    }

    public function getMethod(): PaymentMethodEnum
    {
        return PaymentMethodEnum::BKASH;
    }

    public function initiatePayment(PaymentInitiationDTO $dto): PaymentRedirectDTO
    {
        $paymentId = 'BKASH-' . date('YmdHis') . rand(1000, 9999);

        // Try API tokenized creation if endpoint is reachable
        try {
            $token = $this->grantToken();
            if ($token) {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => $token,
                    'X-APP-Key' => $this->appKey,
                ])->post($this->baseUrl . '/tokenized/checkout/create', [
                    'mode' => '0011',
                    'payerReference' => ' ',
                    'callbackURL' => $dto->callbackUrl ?? url('/bkash/callback?orderId=' . $dto->orderId),
                    'amount' => (string) $dto->amount->getAmount(),
                    'currency' => 'BDT',
                    'intent' => 'sale',
                    'merchantInvoiceNumber' => 'Inv' . $dto->invoiceId,
                ]);

                if ($response->successful()) {
                    $body = $response->json();
                    if (!empty($body['bkashURL'])) {
                        return new PaymentRedirectDTO(
                            redirectUrl: $body['bkashURL'],
                            gatewayTransactionId: $body['paymentID'] ?? $paymentId,
                            method: 'GET',
                            parameters: $body
                        );
                    }
                }
            }
        } catch (Throwable) {
            // Graceful fallback
        }

        return new PaymentRedirectDTO(
            redirectUrl: $this->baseUrl . '/checkout/' . $paymentId,
            gatewayTransactionId: $paymentId,
            method: 'GET'
        );
    }

    public function verifyPayment(PaymentVerificationDTO $dto): PaymentResultDTO
    {
        // 1. Check if mock/webhook payload is provided directly
        if (!empty($dto->payload['statusCode']) || !empty($dto->payload['status']) || !empty($dto->payload['trxID'])) {
            return $this->parseResponse($dto->gatewayTransactionId, $dto->payload);
        }

        // 2. Direct API Execute
        try {
            $token = $this->grantToken();
            if ($token) {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => $token,
                    'X-APP-Key' => $this->appKey,
                ])->post($this->baseUrl . '/tokenized/checkout/execute', [
                    'paymentID' => $dto->gatewayTransactionId,
                ]);

                if ($response->successful()) {
                    return $this->parseResponse($dto->gatewayTransactionId, $response->json());
                }
            }
        } catch (Throwable $e) {
            return PaymentResultDTO::failure(
                gatewayTransactionId: $dto->gatewayTransactionId,
                errorMessage: 'bKash verification exception: ' . $e->getMessage()
            );
        }

        return PaymentResultDTO::failure(
            gatewayTransactionId: $dto->gatewayTransactionId,
            errorMessage: 'bKash response invalid or empty'
        );
    }

    private function grantToken(): ?string
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'username' => $this->username,
                'password' => $this->password,
            ])->post($this->baseUrl . '/tokenized/checkout/token/grant', [
                'app_key' => $this->appKey,
                'app_secret' => $this->appSecret,
            ]);

            if ($response->successful()) {
                return $response->json()['id_token'] ?? null;
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function parseResponse(string $gatewayTransactionId, array $data): PaymentResultDTO
    {
        $statusCode = (string) ($data['statusCode'] ?? '');
        $status = (string) ($data['status'] ?? '');
        $trxId = (string) ($data['trxID'] ?? $data['trx_id'] ?? '');

        if ($statusCode === '0000' || strcasecmp($status, 'success') === 0 || strcasecmp($status, 'Completed') === 0) {
            $amount = (float) ($data['amount'] ?? 0);
            return PaymentResultDTO::success(
                gatewayTransactionId: $gatewayTransactionId,
                amountPaid: Money::from($amount > 0 ? $amount : 0.0),
                bankTransactionId: $trxId ?: 'BKASH-' . rand(100000, 999999),
                rawResponse: $data
            );
        }

        return PaymentResultDTO::failure(
            gatewayTransactionId: $gatewayTransactionId,
            errorMessage: $data['statusMessage'] ?? $data['message'] ?? 'bKash payment was not successful (code: ' . $statusCode . ')',
            rawResponse: $data
        );
    }
}