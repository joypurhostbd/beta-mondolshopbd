<?php

namespace Tests\Unit;

use App\Models\IdempotencyKey;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Payment\Application\Actions\ProcessPaymentWebhookAction;
use Modules\Payment\Application\Services\PaymentGatewayManager;
use Modules\Payment\Domain\Events\PaymentProcessedEvent;
use Modules\Payment\Infrastructure\Gateways\MockPaymentGateway;
use Modules\Payment\Infrastructure\Repositories\EloquentPaymentRepository;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Infrastructure\Services\IdempotencyService;
use Tests\TestCase;

class PaymentWebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    private ProcessPaymentWebhookAction $action;
    private IdempotencyService $idempotencyService;

    protected function setUp(): void
    {
        parent::setUp();

        $manager = new PaymentGatewayManager();
        $manager->register(new MockPaymentGateway(PaymentMethodEnum::BKASH));
        $manager->register(new MockPaymentGateway(PaymentMethodEnum::SHURJOPAY));

        $this->idempotencyService = new IdempotencyService();
        $repository = new EloquentPaymentRepository();

        $this->action = new ProcessPaymentWebhookAction(
            gatewayManager: $manager,
            paymentRepository: $repository,
            idempotencyService: $this->idempotencyService
        );

        Payment::query()->delete();
        IdempotencyKey::query()->delete();

        foreach ([701, 702] as $id) {
            $order = new \App\Models\Order();
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

    public function test_payment_webhook_processes_and_dispatches_event(): void
    {
        Event::fake([PaymentProcessedEvent::class]);

        Payment::create([
            'order_id' => 701,
            'customer_id' => 1,
            'amount' => 1500.0,
            'payment_method' => 'bkash',
            'payment_status' => 'pending',
            'trx_id' => 'TRX-WEBHOOK-701'
        ]);

        $response = $this->action->execute(
            method: PaymentMethodEnum::BKASH,
            transactionId: 'TRX-WEBHOOK-701',
            payload: ['status' => 'success', 'order_id' => 701]
        );

        $this->assertFalse($response['cached']);
        $this->assertEquals(200, $response['status_code']);
        $this->assertTrue($response['body']['success']);
        $this->assertEquals('paid', $response['body']['status']);

        Event::assertDispatched(PaymentProcessedEvent::class, function ($event) {
            return $event->orderId == 701 && $event->status === PaymentStatusEnum::PAID;
        });

        $this->assertTrue($this->idempotencyService->isProcessed('payment_webhook_bkash_TRX-WEBHOOK-701'));
    }

    public function test_duplicate_webhook_returns_cached_response_without_reprocessing(): void
    {
        Event::fake([PaymentProcessedEvent::class]);

        Payment::create([
            'order_id' => 702,
            'customer_id' => 1,
            'amount' => 2200.0,
            'payment_method' => 'bkash',
            'payment_status' => 'pending',
            'trx_id' => 'TRX-WEBHOOK-702'
        ]);

        // First attempt
        $firstResponse = $this->action->execute(
            method: PaymentMethodEnum::BKASH,
            transactionId: 'TRX-WEBHOOK-702',
            payload: ['status' => 'success', 'order_id' => 702]
        );

        $this->assertFalse($firstResponse['cached']);

        // Second duplicate webhook attempt
        $secondResponse = $this->action->execute(
            method: PaymentMethodEnum::BKASH,
            transactionId: 'TRX-WEBHOOK-702',
            payload: ['status' => 'success', 'order_id' => 702]
        );

        $this->assertTrue($secondResponse['cached']);
        $this->assertEquals($firstResponse['body'], $secondResponse['body']);

        // Event should only have been dispatched ONCE
        Event::assertDispatched(PaymentProcessedEvent::class, 1);
    }
}