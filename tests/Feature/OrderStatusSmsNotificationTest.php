<?php

namespace Tests\Feature;

use App\Jobs\SendSmsJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Modules\Order\Domain\Events\OrderStatusChangedEvent;
use Shared\Domain\Enums\OrderStatusEnum;
use Tests\TestCase;

class OrderStatusSmsNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_change_dispatches_sms_job()
    {
        Queue::fake([SendSmsJob::class]);

        $customer = Customer::create([
            'name' => 'SMS Test User',
            'slug' => 'sms-test-user-1',
            'phone' => '01811223344',
            'password' => bcrypt('123456'),
            'verify' => 1,
            'status' => 'active',
        ]);

        $order = new Order();
        $order->invoice_id = 'SMS' . rand(1000, 9999);
        $order->amount = 1200;
        $order->discount = 0;
        $order->shipping_charge = 100;
        $order->customer_id = $customer->id;
        $order->order_status = OrderStatusEnum::PROCESSING->value;
        $order->consignment_id = 'ST-889900';
        $order->save();

        $shipping = new Shipping();
        $shipping->order_id = $order->id;
        $shipping->customer_id = $customer->id;
        $shipping->name = 'SMS Test User';
        $shipping->phone = '01811223344';
        $shipping->address = 'Gulshan, Dhaka';
        $shipping->area = 'Inside Dhaka';
        $shipping->save();

        event(new OrderStatusChangedEvent(
            orderId: $order->id,
            invoiceId: $order->fresh()->invoice_id,
            oldStatus: OrderStatusEnum::PENDING,
            newStatus: OrderStatusEnum::PROCESSING,
        ));

        Queue::assertPushed(SendSmsJob::class, function ($job) use ($order) {
            return $job->phone === '01811223344' && str_contains($job->message, 'is on the way');
        });
    }
}
