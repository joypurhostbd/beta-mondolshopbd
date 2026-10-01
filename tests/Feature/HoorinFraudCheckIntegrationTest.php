<?php

namespace Tests\Feature;

use App\Models\Courierapi;
use App\Models\User;
use App\Services\FraudCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HoorinFraudCheckIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'setting-list', 'guard_name' => 'web']);
        if (!$role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole($role);
    }

    public function test_get_endpoint_url_defaults_to_hoorin_and_respects_custom_or_db_url(): void
    {
        $service = new FraudCheckService();

        // Default when no custom or DB URL set
        $this->assertSame(FraudCheckService::DEFAULT_URL, $service->getEndpointUrl());

        // Custom URL argument takes precedence
        $customUrl = 'https://custom-proxy.hoorin.com/api';
        $this->assertSame($customUrl, $service->getEndpointUrl($customUrl));

        // DB saved URL is respected when no argument provided
        Courierapi::create([
            'type' => 'fraud',
            'url' => 'https://saved-endpoint.example.com/api',
            'token' => 'sample_token',
            'status' => 1,
        ]);

        $this->assertSame('https://saved-endpoint.example.com/api', $service->getEndpointUrl());
    }

    public function test_test_connection_successful(): void
    {
        Http::fake([
            'https://dash.hoorin.com/api/courier/api*' => Http::response([
                'Summaries' => [
                    'Steadfast' => ['Total Parcels' => 5, 'Delivered Parcels' => 4, 'Canceled Parcels' => 1],
                    'Pathao' => ['Total Delivery' => 3, 'Successful Delivery' => 2, 'Canceled Delivery' => 1],
                ],
            ], 200),
        ]);

        $service = new FraudCheckService();
        $result = $service->testConnection('valid_token');

        $this->assertSame('success', $result['status']);
        $this->assertStringContainsString('responsive and ready', $result['message']);
    }

    public function test_test_connection_handles_api_key_error(): void
    {
        Http::fake([
            'https://dash.hoorin.com/api/courier/api*' => Http::response([
                'error' => 'Invalid or Inactive API Key',
            ], 401),
        ]);

        $service = new FraudCheckService();
        $result = $service->testConnection('bad_token');

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('Invalid or Inactive API Key', $result['message']);
    }

    public function test_test_connection_handles_connection_exception_gracefully(): void
    {
        Http::fake([
            'https://dash.hoorin.com/api/courier/api*' => function () {
                throw new ConnectionException('cURL error 28: Operation timed out');
            },
        ]);

        $service = new FraudCheckService();
        $result = $service->testConnection('any_token');

        $this->assertSame('error', $result['status']);
        $this->assertStringContainsString('Connection timed out or network error', $result['message']);
        $this->assertStringContainsString('cURL error 28', $result['message']);
    }

    public function test_admin_can_call_test_fraud_connection_endpoint(): void
    {
        Http::fake([
            'https://dash.hoorin.com/api/courier/api*' => Http::response([
                'Summaries' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('courierapi.fraud.test'), [
            'token' => 'test_token_123',
            'url' => 'https://dash.hoorin.com/api/courier/api',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
        ]);
    }

    public function test_check_calculates_and_caches_delivery_stats(): void
    {
        Courierapi::create([
            'type' => 'fraud',
            'token' => 'active_token',
            'status' => 1,
        ]);

        Http::fake([
            'https://dash.hoorin.com/api/courier/api*' => Http::response([
                'Summaries' => [
                    'Steadfast' => ['Total Parcels' => 10, 'Delivered Parcels' => 8, 'Canceled Parcels' => 2],
                ],
            ], 200),
        ]);

        Cache::flush();
        $service = new FraudCheckService();
        $result = $service->check('01711223344');

        $this->assertSame('success', $result['status']);
        $this->assertEquals(10, $result['total_stats']['total_parcel']);
        $this->assertEquals(8, $result['total_stats']['total_delivered']);
        $this->assertEquals(2, $result['total_stats']['total_cancel']);
        $this->assertEquals(80.0, $result['total_stats']['delivery_rate']);
    }
}
