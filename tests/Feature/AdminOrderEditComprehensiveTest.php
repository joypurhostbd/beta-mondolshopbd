<?php

namespace Tests\Feature;

use App\Enums\OrderStatusEnum;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\ShippingCharge;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderEditComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ShippingCharge $shippingArea;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@mondolshopbd.com',
        ]);

        $this->category = Category::create([
            'name' => 'Apparel',
            'slug' => 'apparel',
            'status' => 1,
        ]);

        OrderStatus::create(['name' => 'Pending', 'slug' => 'pending', 'status' => 1]);
        OrderStatus::create(['name' => 'Delivered', 'slug' => 'delivered', 'status' => 1]);
        OrderStatus::create(['name' => 'Cancelled', 'slug' => 'cancelled', 'status' => 1]);

        $this->shippingArea = ShippingCharge::create([
            'name' => 'Inside Dhaka',
            'amount' => 70,
            'status' => 1,
        ]);
    }

    public function test_order_edit_page_renders_successfully_with_customer_intelligence(): void
    {
        $customer = Customer::create([
            'name' => 'Sizar Babu',
            'slug' => 'sizar-babu',
            'phone' => '01972101994',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '767428937',
            'amount' => 1320,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
            'f_check' => 95,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'name' => 'MD Bakhtiyar Hossain',
            'phone' => '01972101994',
            'address' => 'Gatonshahar',
            'area' => 'Inside Dhaka',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'payment_method' => 'Cash On Delivery',
            'payment_status' => 'pending',
            'amount' => 1320,
        ]);

        $product = Product::create([
            'name' => 'Premium PK Cotton Polo',
            'slug' => 'premium-pk-cotton-polo',
            'product_code' => 'POLO-256',
            'new_price' => 1250,
            'old_price' => 1400,
            'purchase_price' => 500,
            'stock' => 10,
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 500,
            'sale_price' => 1250,
            'product_discount' => 0,
            'product_size' => 'M',
            'product_color' => null,
            'qty' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit', $order->invoice_id));

        $response->assertStatus(200);
        $response->assertSee('Edit Order #'.$order->invoice_id);
        $response->assertSee('MD Bakhtiyar Hossain');
        $response->assertSee('01972101994');
        $response->assertSee('Premium PK Cotton Polo');
        $response->assertSee('95%');
        $response->assertSee('Send to SteadFast Courier');
        $response->assertSee('https://wa.me/8801972101994', false);
        $response->assertDontSee('https://wa.me/881972101994', false);
    }

    public function test_cart_shipping_with_empty_id_does_not_crash_with_500(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.cart_shipping', ['id' => '']));

        $response->assertStatus(200);
        $this->assertEquals('0', (string) $response->getContent());
    }

    public function test_cart_clear_route_supports_both_get_and_post(): void
    {
        $getResponse = $this->actingAs($this->admin)
            ->get(route('admin.order.cart_clear'));
        $getResponse->assertRedirect();

        $postResponse = $this->actingAs($this->admin)
            ->post(route('admin.order.cart_clear'));
        $postResponse->assertRedirect();
    }

    public function test_order_edit_reset_reloads_saved_db_items(): void
    {
        $customer = Customer::create([
            'name' => 'John Doe',
            'slug' => 'john-doe',
            'phone' => '01811111111',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '998877665',
            'amount' => 670,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $product = Product::create([
            'name' => 'Test Item',
            'slug' => 'test-item',
            'product_code' => 'TST-01',
            'new_price' => 600,
            'purchase_price' => 300,
            'stock' => 15,
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 300,
            'sale_price' => 600,
            'qty' => 1,
        ]);

        // First destroy cart
        CartService::instance('pos_shopping')->destroy();
        $this->assertCount(0, CartService::instance('pos_shopping')->content());

        // Call reset route
        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit.reset', $order->invoice_id));

        $response->assertRedirect(route('admin.order.edit', $order->invoice_id));
        $this->assertCount(1, CartService::instance('pos_shopping')->content());
    }

    public function test_order_update_saves_custom_shipping_discount_advance_and_payment_info(): void
    {
        $customer = Customer::create([
            'name' => 'Alice',
            'slug' => 'alice',
            'phone' => '01722222222',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '123456789',
            'amount' => 1070,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $product = Product::create([
            'name' => 'Smart Watch',
            'slug' => 'smart-watch',
            'product_code' => 'SW-01',
            'new_price' => 1000,
            'purchase_price' => 500,
            'stock' => 5,
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        // Populate cart
        CartService::instance('pos_shopping')->destroy();
        CartService::instance('pos_shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 1000,
            'options' => [
                'purchase_price' => 500,
                'product_discount' => 0,
                'product_size' => 'Standard',
                'product_color' => 'Black',
            ],
        ]);

        $postData = [
            'order_id' => $order->id,
            'name' => 'Alice Updated',
            'phone' => '01722222222',
            'address' => 'Mirpur 10, Dhaka',
            'area' => $this->shippingArea->id,
            'custom_shipping' => 120, // Express delivery
            'order_discount' => 50,  // Flat discount
            'advance_amount' => 200, // bKash advance
            'payment_method' => 'bKash',
            'payment_status' => 'paid',
            'trx_id' => 'TRX998877AA',
            'sender_number' => '01722222222',
            'admin_note' => 'Customer confirmed over phone',
            'note' => 'Deliver before 5pm',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.order.update'), $postData);

        $response->assertRedirect(route('admin.orders', 'pending'));

        $order->refresh();
        // Subtotal = 1000, Shipping = 120, Discount = 50, Advance = 200
        // Net COD = (1000 + 120) - 50 - 200 = 870
        $this->assertEquals(870.00, (float) $order->amount);
        $this->assertEquals(50.00, (float) $order->discount);
        $this->assertEquals(120.00, (float) $order->shipping_charge);
        $this->assertEquals('Customer confirmed over phone', $order->admin_note);
        $this->assertEquals('paid', $order->payment_status);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('bKash', $payment->payment_method);
        $this->assertEquals('paid', $payment->payment_status);
        $this->assertEquals('TRX998877AA', $payment->trx_id);
        $this->assertEquals('01722222222', $payment->sender_number);
        $this->assertEquals(200.00, (float) $payment->amount);
    }

    public function test_order_edit_page_renders_courier_controls_when_dispatched(): void
    {
        $customer = Customer::create([
            'name' => 'Dispatched Customer',
            'slug' => 'dispatched-customer',
            'phone' => '01733333333',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '554433221',
            'amount' => 850,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
            'courier_name' => 'steadfast',
            'courier_status' => 'in_transit',
            'consignment_id' => 'CID-998877',
            'tracking_code' => 'TRK-998877',
        ]);

        $product = Product::create([
            'name' => 'Dispatched Polo',
            'slug' => 'dispatched-polo',
            'product_code' => 'DSP-01',
            'new_price' => 780,
            'purchase_price' => 400,
            'stock' => 10,
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 400,
            'sale_price' => 780,
            'qty' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit', $order->invoice_id));

        $response->assertStatus(200);
        $response->assertSee('SteadFast Managed');
        $response->assertSee('CID-998877');
        $response->assertSee('Check Live Status');
        $response->assertSee('Courier Locked');
        $response->assertDontSee('Not Dispatched');
        $response->assertDontSee('Book consignment directly to SteadFast');
        $response->assertDontSee('id="btn-send-single-steadfast"', false);
    }

    public function test_barcode_input_does_not_render_prepend_icon_box(): void
    {
        $customer = Customer::create([
            'name' => 'Barcode Test',
            'slug' => 'barcode-test',
            'phone' => '01711112233',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '445566778',
            'amount' => 500,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit', $order->invoice_id));

        $response->assertStatus(200);
        $response->assertSee('id="barcode_search"', false);
        $response->assertDontSee('<span class="input-group-text bg-light"><i class="fa fa-barcode"></i></span>', false);
    }

    public function test_courier_statuses_are_excluded_from_manual_order_status_dropdown(): void
    {
        foreach (Order::getCourierFilterSlugs() as $courierSlug) {
            OrderStatus::firstOrCreate(
                ['slug' => $courierSlug],
                ['name' => Order::getCourierFilterName($courierSlug), 'status' => 1]
            );
        }

        $customer = Customer::create([
            'name' => 'Status Test Customer',
            'slug' => 'status-test-customer',
            'phone' => '01755554433',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '112233445',
            'amount' => 600,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit', $order->invoice_id));

        $response->assertStatus(200);
        $response->assertSee('name="order_status"', false);
        $response->assertSee('Pending');

        foreach (Order::getCourierFilterSlugs() as $courierSlug) {
            $courierStatusName = Order::getCourierFilterName($courierSlug);
            $response->assertDontSee('<option value="" >'.$courierStatusName.'</option>', false);
        }
    }

    public function test_manual_status_update_rejects_courier_status_ids(): void
    {
        $courierStatus = OrderStatus::firstOrCreate(
            ['slug' => 'courier-shipped'],
            ['name' => 'Courier shipped', 'status' => 1]
        );

        $customer = Customer::create([
            'name' => 'Reject Courier Test',
            'slug' => 'reject-courier-test',
            'phone' => '01788889900',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '998811223',
            'amount' => 1000,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $product = Product::create([
            'name' => 'Test Item',
            'slug' => 'test-item-reject',
            'product_code' => 'REJ-01',
            'new_price' => 1000,
            'purchase_price' => 500,
            'stock' => 5,
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        CartService::instance('pos_shopping')->destroy();
        CartService::instance('pos_shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 1000,
        ]);

        $postData = [
            'order_id' => $order->id,
            'name' => 'Reject Test',
            'phone' => '01788889900',
            'address' => 'Dhaka',
            'area' => $this->shippingArea->id,
            'order_status' => $courierStatus->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.order.update'), $postData);

        $response->assertSessionHasErrors('order_status');
    }

    public function test_cart_item_image_selection_and_order_details_persistence(): void
    {
        $customer = Customer::create([
            'name' => 'Image Test Customer',
            'slug' => 'image-test-customer',
            'phone' => '01799998877',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '334455667',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        $product = Product::create([
            'name' => 'Variant Polo',
            'slug' => 'variant-polo',
            'product_code' => 'VP-01',
            'new_price' => 1200,
            'purchase_price' => 600,
            'stock' => 8,
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        CartService::instance('pos_shopping')->destroy();
        $cartItem = CartService::instance('pos_shopping')->add([
            'id' => $product->id,
            'name' => $product->name,
            'qty' => 1,
            'price' => 1200,
            'options' => [
                'purchase_price' => 600,
                'image' => 'uploads/products/default.jpg',
            ],
        ]);

        // Update image via cart_update_attribute
        $selectedImage = 'uploads/products/red-variant.jpg';
        $updateResponse = $this->actingAs($this->admin)
            ->getJson(route('admin.order.cart_update_attribute', [
                'id' => $cartItem->rowId,
                'image' => $selectedImage,
            ]));

        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['status' => 'success']);

        $updatedCart = CartService::instance('pos_shopping')->get($cartItem->rowId);
        $this->assertEquals($selectedImage, $updatedCart->options->image);

        // Update order and assert product_image is saved in order_details
        $postData = [
            'order_id' => $order->id,
            'name' => 'Image Test Customer',
            'phone' => '01799998877',
            'address' => 'Gulshan 2, Dhaka',
            'area' => $this->shippingArea->id,
        ];

        $saveResponse = $this->actingAs($this->admin)
            ->post(route('admin.order.update'), $postData);

        $saveResponse->assertRedirect(route('admin.orders', 'pending'));

        $detail = OrderDetails::where('order_id', $order->id)->where('product_id', $product->id)->first();
        $this->assertNotNull($detail);
        $this->assertEquals($selectedImage, $detail->product_image);
    }

    public function test_courier_lock_unlocks_when_courier_status_is_cancelled(): void
    {
        $customer = Customer::create([
            'name' => 'Cancelled Courier Customer',
            'slug' => 'cancelled-courier-customer',
            'phone' => '01766667788',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '778899001',
            'amount' => 900,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
            'courier_name' => 'steadfast',
            'courier_status' => 'cancelled', // Courier status is cancelled!
            'consignment_id' => 'CID-CANCELLED-01',
            'tracking_code' => 'TRK-CANCELLED-01',
        ]);

        // When actively in transit -> locked
        $order->courier_status = 'in_transit';
        $this->assertTrue($order->isCourierLocked());

        // When cancelled from courier -> unlocked
        $order->courier_status = 'cancelled';
        $this->assertFalse($order->isCourierLocked());

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit', $order->invoice_id));

        $response->assertStatus(200);
        // Status dropdown is visible (not locked)
        $response->assertSee('name="order_status"', false);
        $response->assertDontSee('(Courier Locked)');
    }

    public function test_order_edit_card_layout_hierarchy_and_compact_sidebar(): void
    {
        $customer = Customer::create([
            'name' => 'Layout Test Customer',
            'slug' => 'layout-test-customer',
            'phone' => '01855554433',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '998877665',
            'amount' => 1500,
            'discount' => 50,
            'shipping_charge' => 120,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'name' => 'Layout Test Customer',
            'phone' => '01855554433',
            'address' => 'Mirpur-10, Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'payment_method' => 'Bkash',
            'payment_status' => 'paid',
            'amount' => 1570,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit', $order->invoice_id));

        $response->assertStatus(200);

        $html = $response->getContent();

        $customerPos = strpos($html, 'Customer & Shipping Info');
        $paymentPos = strpos($html, 'Payment Information');
        $notesPos = strpos($html, 'Order Notes & Instructions');
        $orderStatusCourierPos = strpos($html, 'Order Status & Courier');
        $financialSummaryPos = strpos($html, 'Financial Summary');

        $this->assertNotFalse($customerPos, 'Customer & Shipping Info card should exist');
        $this->assertNotFalse($paymentPos, 'Payment Information card should exist');
        $this->assertNotFalse($notesPos, 'Order Notes & Instructions card should exist');
        $this->assertNotFalse($orderStatusCourierPos, 'Order Status & Courier card should exist');
        $this->assertNotFalse($financialSummaryPos, 'Financial Summary card should exist');

        // Customer & Shipping Info and Payment Info must appear BEFORE Order Notes & Instructions
        $this->assertTrue($customerPos < $notesPos, 'Customer Info must appear before Order Notes');
        $this->assertTrue($paymentPos < $notesPos, 'Payment Info must appear before Order Notes');

        // Order Status & Courier must appear BEFORE Financial Summary on sidebar
        $this->assertTrue($orderStatusCourierPos < $financialSummaryPos, 'Order Status & Courier must appear before Financial Summary');
    }

    public function test_order_edit_currency_inputs_render_cleanly_without_wrapped_symbol_boxes(): void
    {
        $customer = Customer::create([
            'name' => 'Currency Test Customer',
            'slug' => 'currency-test-customer',
            'phone' => '01899998877',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '112233445',
            'amount' => 1200,
            'discount' => 0,
            'shipping_charge' => 80,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'name' => 'Currency Test Customer',
            'phone' => '01899998877',
            'address' => 'Uttara, Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.order.edit', $order->invoice_id));

        $response->assertStatus(200);

        // Ensure table header explicitly states currency
        $response->assertSee('Discount (৳)');

        // Ensure inputs exist with clean attributes and without prepended input-group wrapping
        $response->assertSee('id="custom_shipping"', false);
        $response->assertSee('id="order_discount"', false);
        $response->assertSee('id="advance_amount"', false);

        // Ensure the redundant input-group-text with Taka is removed from these inputs
        $html = $response->getContent();
        $this->assertStringNotContainsString('input-group-text py-0 px-2 font-11">৳', $html);
        $this->assertStringNotContainsString('input-group-text py-0 px-1 font-10">৳', $html);
    }

    public function test_order_update_successfully_updates_order_without_500_error(): void
    {
        $customer = Customer::create([
            'name' => 'Original Customer',
            'slug' => 'original-customer',
            'phone' => '01711112233',
            'password' => bcrypt('secret'),
            'status' => 'active',
        ]);

        $order = Order::create([
            'invoice_id' => '99887766',
            'amount' => 1250,
            'discount' => 0,
            'shipping_charge' => 70,
            'customer_id' => $customer->id,
            'order_status' => OrderStatusEnum::Pending->value,
        ]);

        Shipping::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'name' => 'Original Customer',
            'phone' => '01711112233',
            'address' => 'Mirpur, Dhaka',
            'area' => 'Inside Dhaka',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'payment_method' => 'Cash On Delivery',
            'payment_status' => 'pending',
            'amount' => 1250,
        ]);

        $product = Product::create([
            'name' => 'Update Test Product',
            'slug' => 'update-test-product',
            'product_code' => 'UTP-01',
            'new_price' => 1200,
            'old_price' => 1400,
            'purchase_price' => 500,
            'stock' => 20,
            'status' => 1,
            'category_id' => $this->category->id,
        ]);

        $detail = OrderDetails::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'purchase_price' => 500,
            'sale_price' => 1200,
            'product_discount' => 0,
            'product_size' => 'L',
            'product_color' => 'Blue',
            'qty' => 1,
        ]);

        // 1. Visit edit route to populate pos_shopping Cart
        $this->actingAs($this->admin)->get(route('admin.order.edit', $order->invoice_id));

        $this->assertGreaterThan(0, CartService::instance('pos_shopping')->count());

        // 2. Post update request
        $updateData = [
            'order_id' => $order->id,
            'name' => 'Updated Customer Name',
            'phone' => '01711112233',
            'address' => 'Dhanmondi 27, Dhaka',
            'area' => $this->shippingArea->id,
            'order_status' => OrderStatusEnum::Pending->value,
            'order_discount' => 50,
            'advance_amount' => 200,
            'admin_note' => 'Updated by automated test',
            'payment_method' => 'Cash On Delivery',
            'payment_status' => 'pending',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.order.update'), $updateData);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // 3. Verify database records updated correctly
        $order->refresh();
        $this->assertEquals('Updated by automated test', $order->admin_note);
        $this->assertEquals(50, (float) $order->discount);

        $shipping = Shipping::where('order_id', $order->id)->first();
        $this->assertNotNull($shipping);
        $this->assertEquals('Updated Customer Name', $shipping->name);
        $this->assertEquals('Dhanmondi 27, Dhaka', $shipping->address);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(200, (float) $payment->amount);
    }
}
