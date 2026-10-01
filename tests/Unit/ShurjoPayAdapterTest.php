<?php

namespace Tests\Unit;

use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Infrastructure\Gateways\ShurjoPayPaymentGateway;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Tests\TestCase;

class ShurjoPayAdapterTest extends TestCase
{
    private ShurjoPayPaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new ShurjoPayPaymentGateway();
    }

    public function test_shurjopay_returns_correct_method_enum(): void
    {
        $this->assertEquals(PaymentMethodEnum::SHURJOPAY, $this->gateway->getMethod());
    }

    public function test_shurjopay_initiates_payment_redirect(): void
    {
        $dto = new PaymentInitiationDTO(
            orderId: 501,
            invoiceId: 'INV-501',
            amount: Money::from(2450.0),
            customerName: 'Sizar Babu',
            customerPhone: PhoneNumber::fromString('01711223344'),
            customerEmail: 'sizar@joypurhost.com',
            callbackUrl: 'https://example.com/payment/success',
            cancelUrl: 'https://example.com/payment/cancel'
        );

        $redirect = $this->gateway->initiatePayment($dto);

        $this->assertNotEmpty($redirect->redirectUrl);
        $this->assertNotEmpty($redirect->gatewayTransactionId);
        $this->assertEquals('GET', $redirect->method);
    }

    public function test_shurjopay_successful_verification(): void
    {
        $verifyDTO = new PaymentVerificationDTO(
            gatewayTransactionId: 'NOK2026090310001',
            orderId: 501,
            payload: [
                'sp_code' => 1000,
                'sp_message' => 'Success',
                'bank_trx_id' => 'BANK-TRX-98765',
                'amount' => 2450.0,
                'phone_no' => '01711223344',
                'bank_status' => 'Success'
            ]
        );

        $result = $this->gateway->verifyPayment($verifyDTO);

        $this->assertTrue($result->isSuccessful);
        $this->assertEquals(PaymentStatusEnum::PAID, $result->status);
        $this->assertEquals('NOK2026090310001', $result->gatewayTransactionId);
        $this->assertEquals('BANK-TRX-98765', $result->bankTransactionId);
        $this->assertEquals(2450.0, $result->amountPaid->getAmount());
    }

    public function test_shurjopay_failed_verification(): void
    {
        $verifyDTO = new PaymentVerificationDTO(
            gatewayTransactionId: 'NOK2026090310002',
            orderId: 502,
            payload: [
                'sp_code' => 1005,
                'sp_message' => 'Insufficient Balance / User Cancelled',
                'bank_status' => 'Failed'
            ]
        );

        $result = $this->gateway->verifyPayment($verifyDTO);

        $this->assertFalse($result->isSuccessful);
        $this->assertEquals(PaymentStatusEnum::FAILED, $result->status);
        $this->assertEquals('NOK2026090310002', $result->gatewayTransactionId);
        $this->assertStringContainsString('Insufficient Balance', $result->errorMessage);
    }
}