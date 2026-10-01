<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Productimage;
use App\Models\Shipping;
use App\Models\ShippingCharge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderShippingCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = new User();
        $this->adminUser->name = 'Super Admin';
        $this->adminUser->email = 'admin@mondolshopbd.com';
        $this->adminUser->password = bcrypt('password123');
        $this->adminUser->status = 1;
        $this->adminUser->save();

        $this->createOrderStatus(1, 'Pending', 'pending');
        $this->createOrderStatus(2, 'Processing', 'processing');
        $this->createOrderStatus(3, 'On Hold', 'on-hold');
        $this->createOrderStatus(4, 'In Courier', 'in-courier');
        $this->createOrderStatus(5, 'Confirmed', 'confirmed');
        $this->createOrderStatus(6, 'Completed', 'completed');
        $this->createOrderStatus(7, 'Cancelled', 'cancelled');
        $this->createOrderStatus(8, 'Advance delivery charge', 'advance-delivery-charge');
    }

    protected function createOrderStatus(int $id, string $name, string $slug): OrderStatus
    {
        $status = new OrderStatus();
        $status->id = $id;
        $status->name = $name;
        $status->slug = $slug;
        $status->status = 1;
        $status->save();

        return $status;
    }

    protected function createProduct(array $attributes = []): Product
    {
        $category = new Category();
        $category->name = 'Electronics';
        $category->slug = 'electronics-' . rand(1000, 9999);
        $category->status = 1;
        $category->save();

        $prod = new Product();
        $prod->name = $attributes['name'] ?? 'Smart Watch';
        $prod->slug = $attributes['slug'] ?? 'smart-watch-' . rand(1000, 9999);
        $prod->category_id = $category->id;
        $prod->product_code = $attributes['product_code'] ?? 'P-' . rand(10000, 99999);
        $prod->purchase_price = $attributes['purchase_price'] ?? 800;
        $prod->old_price = $attributes['old_price'] ?? 1500;
        $prod->new_price = $attributes['new_price'] ?? 1200;
        $prod->stock = $attributes['stock'] ?? 20;
        $prod->status = $attributes['status'] ?? 1;
        $prod->save();

        $img = new Productimage();
        $img->product_id = $prod->id;
        $img->image = 'public/uploads/product/default.png';
        $img->save();

        return $prod;
    }

    protected function createShippingCharge(): ShippingCharge
    {
        $shipping = new ShippingCharge();
        $shipping->name = 'Inside Dhaka';
        $shipping->amount = 60;
        $shipping->status = 1;
        $shipping->save();

        return $shipping;
    }

    protected function createOrder(int $statusId = 1): Order
    {
        $customer = new Customer();
        $customer->name = 'Customer One';
        $customer->slug = 'customer-one';
        $customer->phone = '01711223344';
        $customer->password = bcrypt('password123');
        $customer->verify = 1;
        $customer->status = 'active';
        $customer->save();

        $order = new Order();
        $order->invoice_id = 'ORD' . rand(1000, 9999);
        $order->amount = 1260;
        $order->discount = 0;
        $order->shipping_charge = 60;
        $order->customer_id = $customer->id;
        $order->order_status = $statusId;
        $order->save();

        $shipping = new Shipping();
        $shipping->order_id = $order->id;
        $shipping->customer_id = $customer->id;
        $shipping->name = 'Customer One';
        $shipping->phone = '01711223344';
        $shipping->address = 'House 1, Road 2, Gulshan';
        $shipping->area = 'Inside Dhaka';
        $shipping->save();

        $payment = new Payment();
        $payment->order_id = $order->id;
        $payment->customer_id = $customer->id;
        $payment->payment_method = 'Cash on Delivery';
        $payment->amount = 1260;
        $payment->payment_status = 'pending';
        $payment->save();

        $product = $this->createProduct(['purchase_price' => 800, 'new_price' => 1200]);

        $details = new OrderDetails();
        $details->order_id = $order->id;
        $details->product_id = $product->id;
        $details->product_name = $product->name;
        $details->purchase_price = 800;
        $details->sale_price = 1200;
        $details->qty = 1;
        $details->save();

        return $order;
    }

    public function test_admin_can_view_orders_list_by_status_slug()
    {
        $this->createOrder(1);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders', ['slug' => 'all']));

        $response->assertStatus(200);
        $response->assertViewHas('order_status');
    }

    public function test_admin_can_process_order_and_update_status()
    {
        $order = $this->createOrder(1);
        $shippingCharge = $this->createShippingCharge();

        $payload = [
            'id' => $order->id,
            'status' => 2,
            'area' => $shippingCharge->id,
            'name' => 'Updated Customer Name',
            'phone' => '01811223344',
            'address' => 'Updated Dhanmondi Address',
            'admin_note' => 'Call customer before delivery',
        ];

        $response = $this->actingAs($this->adminUser)
            ->from(route('admin.order.process', ['invoice_id' => $order->invoice_id]))
            ->post(route('admin.order_change'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => '2',
            'admin_note' => 'Call customer before delivery',
        ]);
        $this->assertDatabaseHas('shippings', [
            'order_id' => $order->id,
            'name' => 'Updated Customer Name',
            'phone' => '01811223344',
            'address' => 'Updated Dhanmondi Address',
        ]);
    }

    public function test_admin_can_view_order_invoice()
    {
        $order = $this->createOrder(1);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.order.invoice', ['invoice_id' => $order->invoice_id]));

        $response->assertStatus(200);
        $response->assertViewHas('order');
    }

    public function test_admin_stock_report_calculates_stock_and_valuation()
    {
        $this->createProduct(['name' => 'Item A', 'stock' => 10, 'purchase_price' => 100, 'new_price' => 150]);
        $this->createProduct(['name' => 'Item B', 'stock' => 5, 'purchase_price' => 200, 'new_price' => 300]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.stock_report'));

        $response->assertStatus(200);
        $response->assertViewHas('products');
        $response->assertViewHas('total_stock');
        $response->assertViewHas('total_price');
        $response->assertViewHas('total_purchase');
    }

    public function test_admin_order_report_filters_completed_orders()
    {
        $this->createOrder(6); // Delivered / Completed status

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.order_report'));

        $response->assertStatus(200);
        $response->assertViewHas('orders');
        $response->assertViewHas('total_sales');
        $response->assertViewHas('total_purchase');
    }

    public function test_admin_can_update_order_status_inline_and_deduct_inventory()
    {
        $order = $this->createOrder(1); // Pending
        $product = Product::first();
        $initialStock = $product->stock;

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.order.update-status'), [
                'order_id' => $order->id,
                'status_id' => 6, // Completed
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 6,
        ]);
        $this->assertEquals($initialStock - 1, $product->fresh()->stock);
    }

    public function test_admin_can_dispatch_orders_to_pathao_courier()
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://courier-api-sandbox.pathao.com/*' => \Illuminate\Support\Facades\Http::response([
                'type' => 'success',
                'code' => 200,
                'message' => 'Order created successfully',
                'data' => [
                    'consignment_id' => 'PTH-TRK-9988',
                    'order_status' => 'Pending',
                ],
            ], 200),
        ]);

        $courier = new \App\Models\Courierapi();
        $courier->type = 'pathao';
        $courier->url = 'https://courier-api-sandbox.pathao.com';
        $courier->token = 'mock_pathao_token';
        $courier->status = 1;
        $courier->save();

        $order = $this->createOrder(1);

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.order.pathao', [
                'order_ids' => [$order->id],
                'store_id' => 1,
                'city_id' => 1,
                'zone_id' => 1,
                'area_id' => 1,
            ]));

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'consignment_id' => 'PTH-TRK-9988',
        ]);
    }

    public function test_admin_order_index_page_calculates_status_kpi_metrics()
    {
        $order = $this->createOrder(3); // On The Way status
        $order->consignment_id = 'STDF-998877';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders', ['slug' => 'on-the-way']));

        $response->assertStatus(200);
        $response->assertViewHas('status_total_amount');
        $response->assertViewHas('status_dispatched_count');
        $response->assertViewHas('status_today_count');
    }

    public function test_admin_order_cancellation_restores_inventory()
    {
        $order = $this->createOrder(6); // Completed
        $product = Product::first();
        $deliveredStock = $product->stock;

        // Cancel order via inline status update
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.order.update-status'), [
                'order_id' => $order->id,
                'status_id' => 7, // Cancelled
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals($deliveredStock + 1, $product->fresh()->stock);
    }

    public function test_admin_order_update_preserves_invoice_id_and_saves_admin_note()
    {
        $order = $this->createOrder(4); // On Hold
        $originalInvoiceId = $order->invoice_id;
        $shippingCharge = $this->createShippingCharge();

        // Simulate session cart for POS editing
        \App\Services\CartService::instance('pos_shopping')->add([
            'id' => 1,
            'name' => 'Sample Product',
            'qty' => 1,
            'price' => 1000,
            'options' => [
                'slug' => 'sample-product',
                'image' => 'sample.jpg',
                'old_price' => 1200,
                'purchase_price' => 800,
                'product_discount' => 0,
            ],
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.order.update'), [
                'order_id' => $order->id,
                'name' => 'Customer Updated',
                'phone' => '01711223344',
                'address' => 'Updated Dhanmondi 32',
                'area' => $shippingCharge->id,
                'admin_note' => 'Customer requested hold until confirmation',
                'note' => 'Please call in advance',
            ]);

        $freshOrder = $order->fresh();
        $this->assertEquals($originalInvoiceId, $freshOrder->invoice_id);
        $this->assertEquals('Customer requested hold until confirmation', $freshOrder->admin_note);
        $this->assertEquals('Please call in advance', $freshOrder->note);
    }

    public function test_order_edit_and_update_with_added_and_removed_products()
    {
        $order = $this->createOrder(1); // Pending order
        $originalDetail = \App\Models\OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => 10,
            'product_name' => 'Original Old Product',
            'purchase_price' => 100,
            'sale_price' => 200,
            'qty' => 1,
            'product_discount' => 0,
        ]);

        $newProduct = $this->createProduct([
            'name' => 'New Product Added',
            'purchase_price' => 150,
            'new_price' => 300,
            'stock' => 50,
        ]);

        $shippingCharge = $this->createShippingCharge();

        // 1. Visit edit page
        $editResponse = $this->actingAs($this->adminUser)
            ->get(route('admin.order.edit', ['invoice_id' => $order->invoice_id]));
        $editResponse->assertStatus(200);
        $editResponse->assertViewHas('orderstatus');

        // 2. Clear cart and put only the new product (simulating user removing old item and adding new one)
        \App\Services\CartService::instance('pos_shopping')->destroy();
        \App\Services\CartService::instance('pos_shopping')->add([
            'id' => $newProduct->id,
            'name' => $newProduct->name,
            'qty' => 2,
            'price' => $newProduct->new_price,
            'options' => [
                'slug' => $newProduct->slug,
                'image' => '',
                'old_price' => $newProduct->old_price,
                'purchase_price' => $newProduct->purchase_price,
                'product_discount' => 0,
                // Notice details_id is NOT provided (new item from cart_add)
            ],
        ]);

        // 3. Submit update with status change to 2 (Processing)
        $updateResponse = $this->actingAs($this->adminUser)
            ->post(route('admin.order.update'), [
                'order_id' => $order->id,
                'name' => 'Sizar Babu Updated',
                'phone' => '01972101994',
                'address' => 'Gatonshahar, Bogura',
                'area' => $shippingCharge->id,
                'order_status' => 2,
                'admin_note' => 'Confirmed by customer',
                'note' => 'Handle with care',
            ]);

        $updateResponse->assertRedirect();

        // 4. Assert order in database
        $freshOrder = $order->fresh();
        $this->assertEquals(2, $freshOrder->order_status);
        $this->assertEquals('Confirmed by customer', $freshOrder->admin_note);
        $this->assertEquals('Handle with care', $freshOrder->note);
        // Expected amount = (300 * 2) + 60 = 660
        $this->assertEquals(660.00, (float) $freshOrder->amount);

        // 5. Assert shipping updated
        $shipping = \App\Models\Shipping::where('order_id', $order->id)->first();
        $this->assertNotNull($shipping);
        $this->assertEquals('Sizar Babu Updated', $shipping->name);
        $this->assertEquals('01972101994', $shipping->phone);

        // 6. Assert removed old detail was deleted and new detail was created
        $this->assertDatabaseMissing('order_details', ['id' => $originalDetail->id]);
        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $newProduct->id,
            'qty' => 2,
            'sale_price' => 300,
        ]);
    }

    public function test_admin_in_courier_orders_page_loads_with_metrics()
    {
        $order = $this->createOrder(4); // In Courier
        $order->consignment_id = 'STDF-112233';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders', ['slug' => 'in-courier']));

        $response->assertStatus(200);
        $response->assertViewHas('status_total_amount');
        $response->assertViewHas('status_dispatched_count');
    }

    public function test_admin_orders_datatable_renders_multicourier_tracking_links()
    {
        $orderSteadfast = $this->createOrder(4);
        $orderSteadfast->consignment_id = 'STDF-556677';
        $orderSteadfast->save();

        $orderPathao = $this->createOrder(4);
        $orderPathao->consignment_id = 'PTH-889900';
        $orderPathao->save();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.order.data', ['slug' => 'in-courier']));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $json = $response->json();
        $dataStr = json_encode($json['data'] ?? [], JSON_UNESCAPED_SLASHES);
        $this->assertStringContainsString('steadfast.com.bd/t/STDF-556677', $dataStr);
        $this->assertStringContainsString('pathao.com/courier-tracking/?consignment_id=PTH-889900', $dataStr);
    }

    public function test_admin_completed_orders_page_loads_with_metrics()
    {
        $order = $this->createOrder(6); // Completed
        $order->consignment_id = 'STDF-COMPLETED-1';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders', ['slug' => 'completed']));

        $response->assertStatus(200);
        $response->assertViewHas('order_status');
        $response->assertViewHas('status_total_amount');
        $response->assertViewHas('status_dispatched_count');
    }

    public function test_admin_completed_orders_datatable_returns_completed_data()
    {
        $order = $this->createOrder(6); // Completed
        $order->consignment_id = 'STDF-COMPLETED-99';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.order.data', ['slug' => 'completed']));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $json = $response->json();
        $dataStr = json_encode($json['data'] ?? [], JSON_UNESCAPED_SLASHES);
        $this->assertStringContainsString($order->invoice_id, $dataStr);
        $this->assertStringContainsString('STDF-COMPLETED-99', $dataStr);
    }

    public function test_order_status_transition_to_completed_deducts_stock_and_marks_payment_paid()
    {
        $order = $this->createOrder(1); // Pending
        $product = Product::find($order->orderdetails->first()->product_id);
        $initialStock = (int) $product->stock;

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.order.update-status'), [
                'order_id' => $order->id,
                'status_id' => 6, // Completed
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshProduct = $product->fresh();
        $this->assertEquals($initialStock - 1, (int) $freshProduct->stock);

        $freshOrder = $order->fresh();
        $this->assertEquals('paid', $freshOrder->payment_status);

        $freshPayment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals('paid', $freshPayment->payment_status);
    }

    public function test_order_status_transition_from_completed_to_cancelled_restores_stock()
    {
        $order = $this->createOrder(6); // Completed
        $product = Product::find($order->orderdetails->first()->product_id);
        $stockBeforeCancellation = (int) $product->stock;

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.order.update-status'), [
                'order_id' => $order->id,
                'status_id' => 7, // Cancelled
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshProduct = $product->fresh();
        $this->assertEquals($stockBeforeCancellation + 1, (int) $freshProduct->stock);
    }

    public function test_admin_cancelled_orders_page_loads_with_metrics()
    {
        $order = $this->createOrder(7); // Cancelled
        $order->admin_note = 'Customer duplicate order';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders', ['slug' => 'cancelled']));

        $response->assertStatus(200);
        $response->assertViewHas('order_status');
        $response->assertViewHas('status_total_amount');
        $response->assertViewHas('status_dispatched_count');
    }

    public function test_admin_cancelled_orders_datatable_returns_cancelled_data()
    {
        $order = $this->createOrder(7); // Cancelled
        $order->admin_note = 'Fake test cancellation note';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.order.data', ['slug' => 'cancelled']));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $json = $response->json();
        $dataStr = json_encode($json['data'] ?? [], JSON_UNESCAPED_SLASHES);
        $this->assertStringContainsString($order->invoice_id, $dataStr);
        $this->assertStringContainsString('Fake test cancellation note', $dataStr);
    }

    public function test_order_status_transition_to_cancelled_updates_payment_status_cancelled()
    {
        $order = $this->createOrder(1); // Pending

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.order.update-status'), [
                'order_id' => $order->id,
                'status_id' => 7, // Cancelled
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $freshOrder = $order->fresh();
        $this->assertEquals('cancelled', $freshOrder->payment_status);

        $freshPayment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals('cancelled', $freshPayment->payment_status);
    }

    public function test_admin_advance_delivery_charge_orders_page_loads_with_metrics()
    {
        $order = $this->createOrder(8); // Advance delivery charge
        $order->admin_note = 'bKash TrxID #BK889900 advance 120 received';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.orders', ['slug' => 'advance-delivery-charge']));

        $response->assertStatus(200);
        $response->assertViewHas('order_status');
        $response->assertViewHas('status_total_amount');
        $response->assertViewHas('status_dispatched_count');
    }

    public function test_admin_advance_delivery_charge_orders_datatable_returns_data()
    {
        $order = $this->createOrder(8); // Advance delivery charge
        $order->admin_note = 'bKash TrxID #BK889900 advance 120 received';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->getJson(route('admin.order.data', ['slug' => 'advance-delivery-charge']));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $json = $response->json();
        $dataStr = json_encode($json['data'] ?? [], JSON_UNESCAPED_SLASHES);
        $this->assertStringContainsString($order->invoice_id, $dataStr);
        $this->assertStringContainsString('bKash TrxID #BK889900 advance 120 received', $dataStr);
    }

    public function test_admin_order_process_page_renders_with_admin_note_field()
    {
        $order = $this->createOrder(8);
        $order->admin_note = 'bKash TrxID #BK889900 advance 120 received';
        $order->note = 'Customer confirmed via WhatsApp';
        $order->save();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.order.process', ['invoice_id' => $order->invoice_id]));

        $response->assertStatus(200);
        $response->assertSee('Admin Note / Advance Payment TxID');
        $response->assertSee('bKash TrxID #BK889900 advance 120 received');
        $response->assertSee('Customer confirmed via WhatsApp');
    }
}
