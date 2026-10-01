<?php

namespace Tests\Feature;

use App\Events\OrderPlaced;
use App\Jobs\SendSmsJob;
use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\SmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderConfirmationSmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        GeneralSetting::create([
            'name' => 'MondolShopBD',
            'status' => 1,
        ]);
    }

    public function test_order_placed_dispatches_sms_to_client_and_multiple_admin_numbers(): void
    {
        Queue::fake([SendSmsJob::class]);

        SmsGateway::create([
            'provider' => 'joypurhost',
            'url' => 'https://sms.joypurhost.com/api/smsapi',
            'api_key' => 'secret_key',
            'sender_id' => '8809612000000',
            'status' => 1,
            'order' => 1,
            'admin_phone' => '01700112233, 01800112233',
        ]);

        $customer = Customer::create([
            'name' => 'Test Customer',
            'slug' => 'test-customer-1',
            'phone' => '01999887766',
            'password' => bcrypt('password123'),
            'verify' => 1,
            'status' => 1,
        ]);

        $order = new Order();
        $order->invoice_id = 'INV-12345';
        $order->amount = 1500;
        $order->discount = 0;
        $order->shipping_charge = 100;
        $order->customer_id = $customer->id;
        $order->order_status = 1;
        $order->save();

        $shipping = new Shipping();
        $shipping->order_id = $order->id;
        $shipping->customer_id = $customer->id;
        $shipping->name = 'Test Customer';
        $shipping->phone = '01999887766';
        $shipping->address = 'Dhaka, Bangladesh';
        $shipping->area = 'Inside Dhaka';
        $shipping->save();

        $freshOrder = $order->fresh(['shipping', 'customer']);
        event(new OrderPlaced($freshOrder));

        // 1 for client, 2 for admins = 3 jobs
        Queue::assertPushed(SendSmsJob::class, 3);

        // Verify client SMS
        Queue::assertPushed(SendSmsJob::class, function ($job) use ($freshOrder) {
            return $job->phone === '01999887766'
                && str_contains($job->message, (string) $freshOrder->invoice_id)
                && str_contains($job->message, 'placed successfully');
        });

        // Verify admin 1 SMS
        Queue::assertPushed(SendSmsJob::class, function ($job) use ($freshOrder) {
            return $job->phone === '01700112233'
                && str_contains($job->message, 'New Order Alert!')
                && str_contains($job->message, (string) $freshOrder->invoice_id)
                && str_contains($job->message, 'Test Customer');
        });

        // Verify admin 2 SMS
        Queue::assertPushed(SendSmsJob::class, function ($job) use ($freshOrder) {
            return $job->phone === '01800112233'
                && str_contains($job->message, 'New Order Alert!')
                && str_contains($job->message, (string) $freshOrder->invoice_id)
                && str_contains($job->message, '01999887766');
        });
    }

    public function test_order_placed_does_not_dispatch_sms_when_order_toggle_is_disabled(): void
    {
        Queue::fake([SendSmsJob::class]);

        SmsGateway::create([
            'provider' => 'joypurhost',
            'status' => 1,
            'order' => 0, // Disabled
            'admin_phone' => '01700112233',
        ]);

        $order = new Order();
        $order->invoice_id = 'INV-99999';
        $order->amount = 500;
        $order->discount = 0;
        $order->shipping_charge = 0;
        $order->customer_id = 1;
        $order->order_status = 1;
        $order->save();

        $shipping = new Shipping();
        $shipping->order_id = $order->id;
        $shipping->customer_id = 1;
        $shipping->name = 'No SMS User';
        $shipping->phone = '01911000000';
        $shipping->address = 'Dhaka';
        $shipping->area = 'Inside Dhaka';
        $shipping->save();

        event(new OrderPlaced($order->fresh(['shipping'])));

        Queue::assertNothingPushed();
    }

    public function test_order_placed_does_not_dispatch_sms_when_master_status_is_disabled(): void
    {
        Queue::fake([SendSmsJob::class]);

        SmsGateway::create([
            'provider' => 'joypurhost',
            'status' => 0, // Master disabled
            'order' => 1,
            'admin_phone' => '01700112233',
        ]);

        $order = new Order();
        $order->invoice_id = 'INV-88888';
        $order->amount = 800;
        $order->discount = 0;
        $order->shipping_charge = 0;
        $order->customer_id = 1;
        $order->order_status = 1;
        $order->save();

        $shipping = new Shipping();
        $shipping->order_id = $order->id;
        $shipping->customer_id = 1;
        $shipping->name = 'User';
        $shipping->phone = '01911000000';
        $shipping->address = 'Dhaka';
        $shipping->area = 'Inside Dhaka';
        $shipping->save();

        event(new OrderPlaced($order->fresh(['shipping'])));

        Queue::assertNothingPushed();
    }

    public function test_sms_gateway_parses_multiple_admin_phone_numbers(): void
    {
        $gateway = new SmsGateway([
            'admin_phone' => "01711000001, 01811000002 ; 01911000003\n01611000004 , 01711000001",
        ]);

        $numbers = $gateway->getAdminPhoneNumbers();

        $this->assertEquals([
            '01711000001',
            '01811000002',
            '01911000003',
            '01611000004',
        ], $numbers);
    }
}
