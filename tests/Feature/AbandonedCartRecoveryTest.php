<?php

namespace Tests\Feature;

use App\Jobs\SendSmsJob;
use App\Models\IncompleteOrder;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AbandonedCartRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_convert_abandoned_cart_into_order()
    {
        $admin = User::factory()->create();

        OrderStatus::create([
            'name' => 'Pending',
            'slug' => 'pending',
            'status' => 1,
        ]);

        $product = Product::create([
            'name' => 'Premium Smart Watch',
            'slug' => 'premium-smart-watch-1',
            'category_id' => 1,
            'old_price' => 3000,
            'new_price' => 2500,
            'purchase_price' => 1800,
            'stock' => 10,
            'pro_unit' => 'pcs',
            'product_code' => 'SW-01',
            'status' => 1,
        ]);

        $cartData = [
            [
                'id' => $product->id,
                'name' => 'Premium Smart Watch',
                'qty' => 2,
                'price' => 2500,
                'purchase_price' => 1800,
                'options' => (object) [
                    'image' => 'upload/watch.png',
                    'slug' => 'premium-smart-watch',
                    'product_size' => 'M',
                    'product_color' => 'Black',
                ],
            ],
        ];

        $incomplete = IncompleteOrder::create([
            'cart_id' => 'cart_abc_123',
            'name' => 'John Abandoner',
            'phone' => '01988776655',
            'address' => 'Mirpur, Dhaka',
            'data' => json_encode($cartData),
        ]);

        $response = $this->actingAs($admin)->get(route('incomplete.convert', $incomplete->id));

        $response->assertRedirect(route('admin.orders', 'pending'));

        // Verify incomplete order was deleted
        $this->assertDatabaseMissing('incomplete_orders', ['id' => $incomplete->id]);

        // Verify order created
        $this->assertDatabaseHas('orders', [
            'amount' => 5100, // 2500 * 2 + 100 shipping
            'order_status' => 1,
        ]);

        // Verify order detail created with product image
        $this->assertDatabaseHas('order_details', [
            'product_name' => 'Premium Smart Watch',
            'qty' => 2,
            'sale_price' => 2500,
            'product_image' => 'upload/watch.png',
        ]);

        // Verify customer created with active status
        $this->assertDatabaseHas('customers', [
            'phone' => '01988776655',
            'status' => 1,
            'verify' => 1,
        ]);

        // Verify stock was automatically decremented from 10 to 8
        $this->assertEquals(8, $product->fresh()->stock);
    }

    public function test_admin_can_convert_incomplete_order_with_formatted_bengali_phone(): void
    {
        $admin = User::factory()->create();

        $product = Product::create([
            'name' => 'Cotton Polo Shirt',
            'slug' => 'cotton-polo-shirt',
            'category_id' => 1,
            'new_price' => 1000,
            'purchase_price' => 500,
            'stock' => 15,
            'product_code' => 'POLO-01',
            'status' => 1,
        ]);

        $incomplete = IncompleteOrder::create([
            'cart_id' => 'cart_bn_phone_999',
            'name' => 'Karim Rahman',
            'phone' => '+৮৮০১৭১২-৩৪৫৬৭৮',
            'address' => 'Dhanmondi, Dhaka',
            'data' => json_encode([
                [
                    'id' => $product->id,
                    'name' => 'Cotton Polo Shirt',
                    'qty' => 1,
                    'price' => 1000,
                    'purchase_price' => 500,
                ],
            ]),
        ]);

        $response = $this->actingAs($admin)->get(route('incomplete.convert', $incomplete->id));

        $response->assertRedirect(route('admin.orders', 'pending'));
        $this->assertDatabaseMissing('incomplete_orders', ['id' => $incomplete->id]);

        // Customer created with normalized English 11-digit phone and status 1
        $this->assertDatabaseHas('customers', [
            'phone' => '01712345678',
            'status' => 1,
            'verify' => 1,
        ]);

        $this->assertDatabaseHas('shippings', [
            'phone' => '01712345678',
            'name' => 'Karim Rahman',
        ]);
    }

    public function test_cannot_convert_incomplete_order_without_phone(): void
    {
        $admin = User::factory()->create();

        $incomplete = IncompleteOrder::create([
            'cart_id' => 'cart_no_phone_123',
            'name' => 'No Phone Abandoner',
            'phone' => null,
            'address' => 'Mirpur, Dhaka',
            'data' => json_encode([['id' => 1, 'name' => 'Sample', 'price' => 500, 'qty' => 1]]),
        ]);

        $response = $this->actingAs($admin)->get(route('incomplete.convert', $incomplete->id));
        $response->assertRedirect();

        // Ensure lead was NOT converted or deleted
        $this->assertDatabaseHas('incomplete_orders', ['id' => $incomplete->id]);
    }

    public function test_admin_can_send_recovery_sms_to_abandoned_cart_lead()
    {
        Queue::fake([SendSmsJob::class]);

        $admin = User::factory()->create();

        $incomplete = IncompleteOrder::create([
            'cart_id' => 'cart_xyz_789',
            'name' => 'Jane Lead',
            'phone' => '01711998877',
            'address' => 'Uttara, Dhaka',
            'data' => json_encode([]),
        ]);

        $response = $this->actingAs($admin)->get(route('incomplete.sms', $incomplete->id));

        $response->assertRedirect();

        Queue::assertPushed(SendSmsJob::class, function ($job) {
            return $job->phone === '01711998877' && str_contains($job->message, 'you have left items in your cart');
        });
    }

    public function test_admin_can_view_incomplete_orders_list()
    {
        $admin = User::factory()->create();

        $cartData = [
            [
                'id' => 1,
                'name' => 'Leather Wallet',
                'qty' => 1,
                'price' => 1200,
                'options' => (object) [
                    'product_size' => 'Standard',
                    'product_color' => 'Brown',
                ],
            ],
        ];

        IncompleteOrder::create([
            'cart_id' => 'cart_test_view_01',
            'name' => 'View Test Buyer',
            'phone' => '01812345678',
            'address' => 'Dhanmondi, Dhaka',
            'data' => json_encode($cartData),
        ]);

        $response = $this->actingAs($admin)->get(route('incomplete.index'));

        $response->assertStatus(200);
        $response->assertSee('View Test Buyer');
        $response->assertSee('01812345678');
        $response->assertSee('Leather Wallet');
        $response->assertSee('৳1,200.00');
        $response->assertSee('cartItemsModal'); // Single dynamic modal
    }

    public function test_admin_can_delete_incomplete_order()
    {
        $admin = User::factory()->create();

        $incomplete = IncompleteOrder::create([
            'cart_id' => 'cart_delete_test',
            'name' => 'Delete Test Buyer',
            'phone' => '01500000000',
            'address' => 'Gazipur',
            'data' => json_encode([]),
        ]);

        $response = $this->actingAs($admin)->get(route('incomplete.delete', $incomplete->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('incomplete_orders', ['id' => $incomplete->id]);
    }

    public function test_admin_can_bulk_delete_incomplete_orders(): void
    {
        $admin = User::factory()->create();

        $lead1 = IncompleteOrder::create(['cart_id' => 'lead_1', 'phone' => '01700000001', 'data' => json_encode([])]);
        $lead2 = IncompleteOrder::create(['cart_id' => 'lead_2', 'phone' => '01700000002', 'data' => json_encode([])]);
        $lead3 = IncompleteOrder::create(['cart_id' => 'lead_3', 'phone' => '01700000003', 'data' => json_encode([])]);

        $response = $this->actingAs($admin)->post(route('incomplete.bulk_delete'), [
            'ids' => [$lead1->id, $lead2->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('incomplete_orders', ['id' => $lead1->id]);
        $this->assertDatabaseMissing('incomplete_orders', ['id' => $lead2->id]);
        $this->assertDatabaseHas('incomplete_orders', ['id' => $lead3->id]);
    }
}
