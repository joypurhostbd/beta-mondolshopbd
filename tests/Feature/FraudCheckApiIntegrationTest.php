<?php

namespace Tests\Feature;

use App\Models\Courierapi;
use App\Models\User;
use App\Services\FraudCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FraudCheckApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private FraudCheckService $fraudService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);
        $this->fraudService = app(FraudCheckService::class);

        $permissions = [
            'setting-list',
            'setting-create',
            'setting-edit',
            'setting-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
        Cache::flush();
    }

    public function test_calculate_stats_accurately_parses_hoorin_summaries(): void
    {
        $payload = [
            'Summaries' => [
                'Steadfast' => [
                    'Total Parcels' => 5,
                    'Delivered Parcels' => 4,
                    'Canceled Parcels' => 1,
                ],
                'RedX' => [
                    'Total Parcels' => 10,
                    'Delivered Parcels' => 7,
                    'Canceled Parcels' => 3,
                ],
                'Pathao' => [
                    'Total Delivery' => 15,
                    'Successful Delivery' => 14,
                    'Canceled Delivery' => 1,
                ],
                'Carrybee' => [
                    'Total Parcels' => 0,
                    'Delivered Parcels' => 0,
                    'Canceled Parcels' => 0,
                ],
            ],
        ];

        $stats = $this->fraudService->calculateStats($payload);

        $this->assertEquals(30, $stats['total_parcel']);
        $this->assertEquals(25, $stats['total_delivered']);
        $this->assertEquals(5, $stats['total_cancel']);
        $this->assertEquals(83.33, $stats['delivery_rate']);
    }

    public function test_fraud_check_returns_disabled_when_status_is_inactive(): void
    {
        Courierapi::create([
            'type' => 'fraud',
            'token' => 'sample_active_token_123',
            'status' => 0,
        ]);

        Http::fake();

        $result = $this->fraudService->check('01711223344');

        $this->assertEquals('disabled', $result['status']);
        $this->assertEquals('Fraud check is currently disabled', $result['error']);
        Http::assertNothingSent();
    }

    public function test_fraud_check_uses_cache_for_repeated_lookups(): void
    {
        Courierapi::create([
            'type' => 'fraud',
            'token' => 'sample_active_token_123',
            'status' => 1,
        ]);

        Http::fake([
            'https://dash.hoorin.com/*' => Http::response([
                'Summaries' => [
                    'Pathao' => [
                        'Total Delivery' => 10,
                        'Successful Delivery' => 9,
                        'Canceled Delivery' => 1,
                    ],
                ],
            ], 200),
        ]);

        // First call triggers network
        $res1 = $this->fraudService->check('01799887766');
        $this->assertEquals('success', $res1['status']);
        $this->assertEquals(90.0, $res1['total_stats']['delivery_rate']);
        Http::assertSentCount(1);

        // Second call should come directly from cache
        $res2 = $this->fraudService->check('01799887766');
        $this->assertEquals('success', $res2['status']);
        $this->assertEquals(90.0, $res2['total_stats']['delivery_rate']);
        Http::assertSentCount(1);
    }

    public function test_guest_cannot_access_fraud_test_endpoint(): void
    {
        $response = $this->postJson(route('courierapi.fraud.test'), ['token' => 'test_token']);
        $response->assertStatus(401);
    }

    public function test_admin_can_test_fraud_connection_successfully(): void
    {
        Http::fake([
            'https://dash.hoorin.com/*' => Http::response([
                'Summaries' => [
                    'Steadfast' => [
                        'Total Parcels' => 0,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('courierapi.fraud.test'), [
            'token' => 'valid_fraud_token_abc',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
        ]);
    }

    public function test_admin_can_refresh_order_fraud_score(): void
    {
        \App\Models\Courierapi::create([
            'type' => 'fraud',
            'token' => 'sample_token_xyz',
            'status' => 1,
        ]);

        $customer = \App\Models\Customer::create([
            'name' => 'Mamun Bepari',
            'slug' => 'mamun-bepari',
            'phone' => '01973908038',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        $order = \App\Models\Order::create([
            'invoice_id' => 998877,
            'customer_id' => $customer->id,
            'order_status' => 1,
            'amount' => 1000,
            'discount' => 0,
            'shipping_charge' => 60,
            'f_check' => 0,
        ]);

        \App\Models\Shipping::create([
            'order_id' => $order->id,
            'customer_id' => $customer->id,
            'name' => 'Mamun Bepari',
            'phone' => '01973908038',
            'address' => 'Narayanganj',
            'area' => 'Narayanganj',
        ]);

        Http::fake([
            'https://dash.hoorin.com/*' => Http::response([
                'Summaries' => [
                    'RedX' => [
                        'Total Parcels' => 10,
                        'Delivered Parcels' => 6,
                        'Canceled Parcels' => 4,
                    ],
                    'Pathao' => [
                        'Total Delivery' => 1,
                        'Successful Delivery' => 1,
                        'Canceled Delivery' => 0,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('admin.order.refresh_fraud', $order->id), [
            'force' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'delivery_rate' => 63.64,
            'formatted_rate' => '64%',
            'badge_class' => 'bg-warning',
        ]);

        $this->assertEquals(63.64, (float) $order->fresh()->f_check);
    }
}
