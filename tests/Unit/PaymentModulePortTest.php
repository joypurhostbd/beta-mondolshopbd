<?php

namespace Tests\Unit;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Application\Services\PaymentGatewayManager;
use Modules\Payment\Application\Services\PaymentService;
use Modules\Payment\Domain\Events\PaymentProcessedEvent;
use Shared\Domain\Contracts\Modules\PaymentModuleInterface;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Tests\TestCase;

class PaymentModulePortTest extends TestCase
{
    use RefreshDatabase;

    private PaymentGatewayManager $gatewayManager;
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $this->paymentService = $this->app->make(PaymentService::class);

        $order = new \App\Models\Order();
        $order->id = 202;
        $order->invoice_id = 'INV-202';
        $order->amount = 2500;
        $order->discount = 0;
        $order->shipping_charge = 0;
        $order->customer_id = 1;
        $order->order_status = 1;
        $order->save();
    }

    public function test_payment_gateway_manager_resolves_registered_gateways(): void
    {
        $this->assertTrue($this->gatewayManager->has(PaymentMethodEnum::COD));
        $this->assertTrue($this->gatewayManager->has(PaymentMethodEnum::SHURJOPAY));
        $this->assertTrue($this->gatewayManager->has(PaymentMethodEnum::BKASH));

        $codGateway = $this->gatewayManager->resolve(PaymentMethodEnum::COD);
        $this->assertEquals(PaymentMethodEnum::COD, $codGateway->getMethod());
    }

    public function test_gateway_initiation_and_verification(): void
    {
        $gateway = $this->gatewayManager->resolve(PaymentMethodEnum::SHURJOPAY);

        $initDTO = new PaymentInitiationDTO(
            orderId: 101,
            invoiceId: 'INV-101',
            amount: Money::from(1500.0),
            customerName: 'Sizar Babu',
            customerPhone: PhoneNumber::fromString('01711223344'),
            callbackUrl: 'https://example.com/payment/callback'
        );

        $redirect = $gateway->initiatePayment($initDTO);
        $this->assertNotEmpty($redirect->redirectUrl);
        $this->assertNotEmpty($redirect->gatewayTransactionId);

        $verifyDTO = new PaymentVerificationDTO(
            gatewayTransactionId: $redirect->gatewayTransactionId,
            orderId: 101,
            payload: ['status' => 'success']
        );

        $result = $gateway->verifyPayment($verifyDTO);
        $this->assertTrue($result->isSuccessful);
        $this->assertEquals(PaymentStatusEnum::PAID, $result->status);
    }

    public function test_payment_service_implements_module_interface(): void
    {
        Event::fake([PaymentProcessedEvent::class]);

        $this->assertInstanceOf(PaymentModuleInterface::class, $this->paymentService);

        // Process initial payment
        $paymentData = $this->paymentService->processPayment(
            orderId: 202,
            amount: Money::from(2500.0),
            method: PaymentMethodEnum::SHURJOPAY
        );

        $this->assertEquals(202, $paymentData['order_id']);
        $this->assertEquals(2500.0, $paymentData['amount']);

        // Insert transaction ID for verification
        $paymentModel = Payment::where('order_id', 202)->first();
        $paymentModel->trx_id = 'TRX-VERIFY-999';
        $paymentModel->save();

        // Verify Payment
        $verified = $this->paymentService->verifyPayment('TRX-VERIFY-999', ['status' => 'success']);
        $this->assertTrue($verified);

        Event::assertDispatched(PaymentProcessedEvent::class, function ($event) {
            return $event->orderId == 202 && $event->status === PaymentStatusEnum::PAID;
        });
    }
}