<?php

namespace Tests\Unit;

use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Infrastructure\Gateways\BkashPaymentGateway;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Tests\TestCase;

class BkashAdapterTest extends TestCase
{
    private BkashPaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new BkashPaymentGateway();
    }

    public function test_bkash_returns_correct_method_enum(): void
    {
        $this->assertEquals(PaymentMethodEnum::BKASH, $this->gateway->getMethod());
    }

    public function test_bkash_initiates_payment_redirect(): void
    {
        $dto = new PaymentInitiationDTO(
            orderId: 601,
            invoiceId: 'INV-601',
            amount: Money::from(1800.0),
            customerName: 'Sizar Babu',
            customerPhone: PhoneNumber::fromString('01711223344'),
            customerEmail: 'sizar@joypurhost.com',
            callbackUrl: 'https://example.com/payment/bkash/callback'
        );

        $redirect = $this->gateway->initiatePayment($dto);

        $this->assertNotEmpty($redirect->redirectUrl);
        $this->assertNotEmpty($redirect->gatewayTransactionId);
        $this->assertEquals('GET', $redirect->method);
    }

    public function test_bkash_successful_verification(): void
    {
        $verifyDTO = new PaymentVerificationDTO(
            gatewayTransactionId: 'PAY-BKASH-90001',
            orderId: 601,
            payload: [
                'statusCode' => '0000',
                'statusMessage' => 'Successful',
                'paymentID' => 'PAY-BKASH-90001',
                'trxID' => '9H786GH12',
                'amount' => 1800.0,
                'customerMsisdn' => '01711223344',
                'status' => 'Completed'
            ]
        );

        $result = $this->gateway->verifyPayment($verifyDTO);

        $this->assertTrue($result->isSuccessful);
        $this->assertEquals(PaymentStatusEnum::PAID, $result->status);
        $this->assertEquals('PAY-BKASH-90001', $result->gatewayTransactionId);
        $this->assertEquals('9H786GH12', $result->bankTransactionId);
        $this->assertEquals(1800.0, $result->amountPaid->getAmount());
    }

    public function test_bkash_failed_verification(): void
    {
        $verifyDTO = new PaymentVerificationDTO(
            gatewayTransactionId: 'PAY-BKASH-90002',
            orderId: 602,
            payload: [
                'statusCode' => '2023',
                'statusMessage' => 'Insufficient Balance',
                'status' => 'failure'
            ]
        );

        $result = $this->gateway->verifyPayment($verifyDTO);

        $this->assertFalse($result->isSuccessful);
        $this->assertEquals(PaymentStatusEnum::FAILED, $result->status);
        $this->assertEquals('PAY-BKASH-90002', $result->gatewayTransactionId);
        $this->assertStringContainsString('Insufficient Balance', $result->errorMessage);
    }
}