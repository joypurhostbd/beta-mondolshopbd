<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Infrastructure\Http\Resources\CartItemResource;
use Shared\Infrastructure\Http\Resources\CustomerResource;
use Shared\Infrastructure\Http\Resources\OrderResource;
use Shared\Infrastructure\Http\Resources\ProductResource;
use Shared\Infrastructure\Http\Responses\ApiResponse;
use Tests\TestCase;

class ApiResourcesStandardizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_response_envelope_structures(): void
    {
        $success = ApiResponse::success(['token' => 'abc-123'], 'Login successful');
        $this->assertEquals(200, $success->getStatusCode());
        $successData = json_decode($success->getContent(), true);
        $this->assertTrue($successData['success']);
        $this->assertEquals('success', $successData['status']);
        $this->assertEquals('Login successful', $successData['message']);
        $this->assertEquals('abc-123', $successData['data']['token']);

        $error = ApiResponse::error('Validation failed', ['phone' => ['Invalid phone']], 422);
        $this->assertEquals(422, $error->getStatusCode());
        $errorData = json_decode($error->getContent(), true);
        $this->assertFalse($errorData['success']);
        $this->assertEquals('error', $errorData['status']);
        $this->assertEquals('Validation failed', $errorData['message']);
        $this->assertArrayHasKey('phone', $errorData['errors']);

        $paginated = ApiResponse::paginated([['id' => 1]], ['total' => 1, 'per_page' => 15]);
        $this->assertEquals(200, $paginated->getStatusCode());
        $paginatedData = json_decode($paginated->getContent(), true);
        $this->assertEquals(1, $paginatedData['meta']['total']);
    }

    public function test_product_resource_serialization(): void
    {
        $product = new Product([
            'name' => 'Premium Polo T-Shirt',
            'slug' => 'premium-polo-t-shirt',
            'new_price' => 850.0,
            'old_price' => 1050.0,
            'stock' => 25,
            'image_one' => 'uploads/polo.jpg',
            'status' => 1,
        ]);
        $product->id = 77;

        $resource = new ProductResource($product);
        $data = $resource->toArray(Request::create('/api/products/77'));

        $this->assertEquals(77, $data['id']);
        $this->assertEquals('Premium Polo T-Shirt', $data['name']);
        $this->assertEquals(850.0, $data['price']);
        $this->assertEquals(1050.0, $data['old_price']);
        $this->assertEquals(25, $data['stock']);
        $this->assertTrue($data['is_active']);
    }

    public function test_order_resource_serialization(): void
    {
        $order = new Order([
            'invoice_id' => 'INV-2026-99',
            'customer_name' => 'Sizar Babu',
            'customer_phone' => '01711223344',
            'shipping_address' => 'Dhaka, Bangladesh',
            'order_status' => OrderStatusEnum::PROCESSING->value,
            'subtotal' => 2000.0,
            'shipping_charge' => 60.0,
            'discount' => 100.0,
            'amount' => 1960.0,
            'total_amount' => 1960.0,
        ]);
        $order->id = 99;
        $order->courier_name = 'Steadfast';
        $order->courier_status = 'STDF-998877';

        $resource = new OrderResource($order);
        $data = $resource->toArray(Request::create('/api/orders/99'));

        $this->assertEquals(99, $data['id']);
        $this->assertEquals('INV-2026-99', $data['invoice_id']);
        $this->assertEquals('Processing', $data['status']['label']);
        $this->assertEquals(1960.0, $data['pricing']['total']);
        $this->assertEquals('Steadfast', $data['courier']['name']);
    }

    public function test_cart_item_and_customer_resource_serialization(): void
    {
        $cartObj = (object) [
            'rowId' => 'row-xyz-123',
            'product_id' => 15,
            'name' => 'Leather Wallet',
            'price' => 1200.0,
            'qty' => 2,
            'options' => (object) [
                'color' => 'Black',
                'size' => null,
                'image' => 'uploads/wallet.jpg',
            ],
        ];

        $cartResource = new CartItemResource($cartObj);
        $cartData = $cartResource->toArray(Request::create('/api/cart'));

        $this->assertEquals('row-xyz-123', $cartData['row_id']);
        $this->assertEquals(2400.0, $cartData['subtotal']);
        $this->assertEquals('Black', $cartData['options']['color']);

        $customer = new Customer([
            'name' => 'Sizar Babu',
            'phone' => '01711223344',
            'email' => 'ceo@joypurhost.com',
            'address' => 'Banani, Dhaka',
            'status' => 'active',
            'password' => '$2y$10$secret_hash_not_exposed',
        ]);
        $customer->id = 1;

        $custResource = new CustomerResource($customer);
        $custData = $custResource->toArray(Request::create('/api/customer/profile'));

        $this->assertEquals(1, $custData['id']);
        $this->assertEquals('ceo@joypurhost.com', strtolower($custData['email']));
        $this->assertArrayNotHasKey('password', $custData);
    }
}