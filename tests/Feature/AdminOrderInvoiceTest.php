<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = new User();
        $this->adminUser->name = 'Admin Tester';
        $this->adminUser->email = 'admin@mondolshopbd.com';
        $this->adminUser->password = bcrypt('password123');
        $this->adminUser->status = 1;
        $this->adminUser->save();

        $this->createOrderStatus(1, 'Pending', 'pending');
        $this->createOrderStatus(4, 'In Courier', 'in-courier');
    }

    private function createOrderStatus(int $id, string $name, string $slug): OrderStatus
    {
        $status = new OrderStatus();
        $status->id = $id;
        $status->name = $name;
        $status->slug = $slug;
        $status->status = 1;
        $status->save();
        return $status;
    }

    public function test_admin_can_view_standard_invoice_without_in_review_status(): void
    {
        $order = Order::create([
            'invoice_id' => '653338092',
            'amount' => 1500.00,
            'discount' => 50.00,
            'shipping_charge' => 100.00,
            'customer_id' => 1,
            'order_status' => 4,
            'consignment_id' => '295017297',
            'courier_name' => 'steadfast',
            'courier_status' => 'in_review',
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => 10,
            'purchase_price' => 1000.00,
            'product_name' => 'Premium Men Panjabi',
            'product_size' => 'L',
            'product_color' => 'White',
            'sale_price' => 1450.00,
            'qty' => 1,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Tanvir Ahmed',
            'phone' => '01899999999',
            'address' => 'House 12, Road 4, Dhanmondi',
            'area' => 'Inside Dhaka',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'payment_method' => 'Cash on Delivery',
            'amount' => 1500.00,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.order.invoice', ['invoice_id' => $order->invoice_id]));

        $response->assertOk();
        $response->assertViewHas('order');
        $response->assertViewHas('invoice');

        // Check standard invoice layout elements
        $response->assertSee('INVOICE');
        $response->assertSee('653338092');
        $response->assertSee('Tanvir Ahmed');
        $response->assertSee('01899999999');
        $response->assertSee('House 12, Road 4, Dhanmondi');
        $response->assertSee('Premium Men Panjabi');
        $response->assertSee('Size: L');
        $response->assertSee('Color: White');

        // Courier details MUST be present
        $response->assertSee('SteadFast');
        $response->assertSee('Tracking ID');
        $response->assertSee('295017297');

        // CRITICAL: "Status: in review" MUST NOT appear
        $response->assertDontSee('Status: in review', false);
        $response->assertDontSee('Status: In review', false);
        $response->assertDontSee('in_review', false);
    }

    public function test_admin_can_view_pos_invoice_without_in_review_status(): void
    {
        $order = Order::create([
            'invoice_id' => '653338092',
            'amount' => 1500.00,
            'discount' => 50.00,
            'shipping_charge' => 100.00,
            'customer_id' => 1,
            'order_status' => 4,
            'consignment_id' => '295017297',
            'courier_name' => 'steadfast',
            'courier_status' => 'in_review',
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => 10,
            'purchase_price' => 1000.00,
            'product_name' => 'Premium Men Panjabi',
            'sale_price' => 1450.00,
            'qty' => 1,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => 1,
            'name' => 'Tanvir Ahmed',
            'phone' => '01899999999',
            'address' => 'Dhanmondi, Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.order.invoice', ['invoice_id' => $order->invoice_id, 'pos' => 'true']));

        $response->assertOk();
        $response->assertSee('pos-receipt-card');
        $response->assertSee('653338092');
        $response->assertSee('Tanvir Ahmed');
        $response->assertSee('01899999999');

        // Courier details
        $response->assertSee('SteadFast');
        $response->assertSee('Tracking ID');
        $response->assertSee('295017297');

        // CRITICAL: "Status: in review" MUST NOT appear on POS slip
        $response->assertDontSee('Status: in review', false);
        $response->assertDontSee('Status: In review', false);
        $response->assertDontSee('in_review', false);

        // Verify Thermal Print button and single-page print rules
        $response->assertSee('Thermal Print');
        $response->assertSee('printThermal()', false);
        $response->assertSee('size: 3in 4in', false);
        $response->assertSee('max-height: 4in', false);
        $response->assertSee('.pos-footer-text', false);
        $response->assertSee('display: none !important', false);
        $response->assertSee('page-break-inside: avoid', false);

        // Verify unwanted elements are removed from POS slip
        $response->assertDontSee('Payment: Cash on Delivery', false);
        $response->assertDontSee('Area: Inside Dhaka', false);
    }
}
