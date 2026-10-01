<?php

namespace Tests\Feature;

use App\Models\IdempotencyKey;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Payment\Application\Actions\ProcessPaymentWebhookAction;
use Modules\Payment\Application\DTOs\PaymentInitiationDTO;
use Modules\Payment\Application\DTOs\PaymentVerificationDTO;
use Modules\Payment\Application\Services\PaymentGatewayManager;
use Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use Modules\Payment\Domain\Events\PaymentProcessedEvent;
use Shared\Domain\Contracts\Modules\PaymentModuleInterface;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Tests\TestCase;

class PaymentModuleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private PaymentModuleInterface $paymentModule;
    private PaymentGatewayManager $gatewayManager;
    private ProcessPaymentWebhookAction $webhookAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentModule = $this->app->make(PaymentModuleInterface::class);
        $this->gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $this->webhookAction = $this->app->make(ProcessPaymentWebhookAction::class);

        Payment::query()->delete();
        IdempotencyKey::query()->delete();
        Order::query()->delete();

        foreach ([1001, 1002, 1003, 1004, 1005] as $id) {
            $order = new Order();
            $order->id = $id;
            $order->invoice_id = 'INV-' . $id;
            $order->amount = 5000;
            $order->discount = 0;
            $order->shipping_charge = 0;
            $order->customer_id = 1;
            $order->order_status = 1;
            $order->save();
        }
    }

    public function test_full_cod_payment_lifecycle(): void
    {
        $orderId = 1001;
        $amount = Money::from(3200.50);

        $result = $this->paymentModule->processPayment($orderId, $amount, PaymentMethodEnum::COD);

        $this->assertEquals($orderId, $result['order_id']);
        $this->assertEquals(3200.50, $result['amount']);
        $this->assertEquals('cod', $result['payment_method']);
        $this->assertEquals('pending', $result['payment_status']);

        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'amount' => 3200.50,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
        ]);
    }

    public function test_full_shurjopay_payment_lifecycle(): void
    {
        Event::fake([PaymentProcessedEvent::class]);

        $gateway = $this->gatewayManager->resolve(PaymentMethodEnum::SHURJOPAY);

        // 1. Initiate Payment
        $initiationDTO = new PaymentInitiationDTO(
            orderId: 1002,
            invoiceId: 'INV-1002',
            amount: Money::from(4500.00),
            customerName: 'Sizar Babu',
            customerPhone: PhoneNumber::fromString('01711223344'),
            customerEmail: 'sizar@joypurhost.com',
            callbackUrl: 'https://mondolshop.com/payment/shurjopay/callback'
        );

        $redirect = $gateway->initiatePayment($initiationDTO);
        $this->assertNotEmpty($redirect->redirectUrl);
        $this->assertNotEmpty($redirect->gatewayTransactionId);

        // 2. Persist Payment record
        $paymentData = $this->paymentModule->processPayment(1002, Money::from(4500.00), PaymentMethodEnum::SHURJOPAY);
        $paymentModel = Payment::where('order_id', 1002)->first();
        $paymentModel->trx_id = $redirect->gatewayTransactionId;
        $paymentModel->save();

        // 3. Verify Payment
        $verified = $this->paymentModule->verifyPayment($redirect->gatewayTransactionId, [
            'sp_code' => 1000,
            'sp_message' => 'Success',
            'bank_trx_id' => 'BANK-SP-88899',
            'amount' => 4500.00,
        ]);

        $this->assertTrue($verified);

        $this->assertDatabaseHas('payments', [
            'order_id' => 1002,
            'trx_id' => $redirect->gatewayTransactionId,
            'payment_status' => 'paid',
        ]);

        Event::assertDispatched(PaymentProcessedEvent::class, function ($event) {
            return $event->orderId == 1002 && $event->status === PaymentStatusEnum::PAID;
        });
    }

    public function test_full_bkash_payment_lifecycle(): void
    {
        Event::fake([PaymentProcessedEvent::class]);

        $gateway = $this->gatewayManager->resolve(PaymentMethodEnum::BKASH);

        // 1. Initiate Payment
        $initiationDTO = new PaymentInitiationDTO(
            orderId: 1003,
            invoiceId: 'INV-1003',
            amount: Money::from(1950.00),
            customerName: 'Sizar Babu',
            customerPhone: PhoneNumber::fromString('01711223344'),
            customerEmail: 'sizar@joypurhost.com',
            callbackUrl: 'https://mondolshop.com/payment/bkash/callback'
        );

        $redirect = $gateway->initiatePayment($initiationDTO);
        $this->assertNotEmpty($redirect->redirectUrl);
        $this->assertNotEmpty($redirect->gatewayTransactionId);

        // 2. Persist Payment record
        $this->paymentModule->processPayment(1003, Money::from(1950.00), PaymentMethodEnum::BKASH);
        $paymentModel = Payment::where('order_id', 1003)->first();
        $paymentModel->trx_id = $redirect->gatewayTransactionId;
        $paymentModel->save();

        // 3. Verify Payment
        $verified = $this->paymentModule->verifyPayment($redirect->gatewayTransactionId, [
            'statusCode' => '0000',
            'statusMessage' => 'Successful',
            'trxID' => 'BKASH-TRX-12345',
            'amount' => 1950.00,
            'status' => 'Completed',
        ]);

        $this->assertTrue($verified);

        $this->assertDatabaseHas('payments', [
            'order_id' => 1003,
            'trx_id' => $redirect->gatewayTransactionId,
            'payment_status' => 'paid',
        ]);

        Event::assertDispatched(PaymentProcessedEvent::class, function ($event) {
            return $event->orderId == 1003 && $event->status === PaymentStatusEnum::PAID;
        });
    }

    public function test_idempotent_webhook_processing_prevents_duplicate_events(): void
    {
        Event::fake([PaymentProcessedEvent::class]);

        Payment::create([
            'order_id' => 1004,
            'customer_id' => 1,
            'amount' => 5000.0,
            'payment_method' => 'bkash',
            'payment_status' => 'pending',
            'trx_id' => 'TRX-IDEMPOTENT-1004',
        ]);

        // First Webhook Delivery
        $firstCall = $this->webhookAction->execute(
            method: PaymentMethodEnum::BKASH,
            transactionId: 'TRX-IDEMPOTENT-1004',
            payload: [
                'statusCode' => '0000',
                'trxID' => 'BANK-IDEM-001',
                'amount' => 5000.0,
                'order_id' => 1004,
            ]
        );

        $this->assertFalse($firstCall['cached']);
        $this->assertTrue($firstCall['body']['success']);
        $this->assertEquals('paid', $firstCall['body']['status']);

        // Duplicate Webhook Delivery
        $secondCall = $this->webhookAction->execute(
            method: PaymentMethodEnum::BKASH,
            transactionId: 'TRX-IDEMPOTENT-1004',
            payload: [
                'statusCode' => '0000',
                'trxID' => 'BANK-IDEM-001',
                'amount' => 5000.0,
                'order_id' => 1004,
            ]
        );

        $this->assertTrue($secondCall['cached']);
        $this->assertEquals($firstCall['body'], $secondCall['body']);

        // Dispatched only once
        Event::assertDispatched(PaymentProcessedEvent::class, 1);
    }

    public function test_failed_payment_verification_updates_status(): void
    {
        Event::fake([PaymentProcessedEvent::class]);

        Payment::create([
            'order_id' => 1005,
            'customer_id' => 1,
            'amount' => 1200.0,
            'payment_method' => 'shurjopay',
            'payment_status' => 'pending',
            'trx_id' => 'TRX-FAIL-1005',
        ]);

        $verified = $this->paymentModule->verifyPayment('TRX-FAIL-1005', [
            'sp_code' => 1005,
            'sp_message' => 'User Cancelled',
            'bank_status' => 'Failed',
        ]);

        $this->assertFalse($verified);

        $this->assertDatabaseHas('payments', [
            'order_id' => 1005,
            'trx_id' => 'TRX-FAIL-1005',
            'payment_status' => 'failed',
        ]);

        Event::assertDispatched(PaymentProcessedEvent::class, function ($event) {
            return $event->orderId == 1005 && $event->status === PaymentStatusEnum::FAILED;
        });
    }
}