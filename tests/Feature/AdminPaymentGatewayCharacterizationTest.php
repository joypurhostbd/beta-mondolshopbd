<?php

namespace Tests\Feature;

use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminPaymentGatewayCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

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
    }

    public function test_guest_cannot_access_payment_gateway_manage(): void
    {
        $response = $this->get(route('paymentgeteway.manage'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_without_permissions_is_forbidden(): void
    {
        $plainUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($plainUser)->get(route('paymentgeteway.manage'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_payment_gateway_manage_with_kpis(): void
    {
        PaymentGateway::create([
            'type' => 'bkash',
            'username' => 'bkash_user',
            'app_key' => 'key_123',
            'app_secret' => 'secret_123',
            'password' => 'pass_123',
            'base_url' => 'https://tokenized.pay.bka.sh/v1.2.0-beta',
            'status' => 1,
        ]);

        PaymentGateway::create([
            'type' => 'shurjopay',
            'username' => 'sp_user',
            'prefix' => 'SP',
            'password' => 'sp_pass',
            'base_url' => 'https://engine.shurjopayment.com',
            'success_url' => 'https://example.com/success',
            'return_url' => 'https://example.com/return',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('paymentgeteway.manage'));

        $response->assertStatus(200);
        $response->assertSee('Payment Gateway Configuration');
        $response->assertSee('Total Gateways');
        $response->assertSee('Active Gateways');
        $response->assertSee('bKash PGW');
        $response->assertSee('Shurjopay Payment Gateway');
        $response->assertSee('bkash_user');
        $response->assertSee('sp_user');
    }

    public function test_admin_can_update_bkash_gateway_settings(): void
    {
        $bkash = PaymentGateway::create([
            'type' => 'bkash',
            'username' => 'old_user',
            'app_key' => 'old_key',
            'app_secret' => 'old_secret',
            'password' => 'old_pass',
            'base_url' => 'https://old.bka.sh',
            'status' => 0,
        ]);

        $payload = [
            'id' => $bkash->id,
            'type' => 'bkash',
            'username' => 'new_bkash_user',
            'app_key' => 'new_app_key_xyz',
            'app_secret' => 'new_secret_xyz',
            'password' => 'new_password_xyz',
            'base_url' => 'https://tokenized.pay.bka.sh/v1.2.0-beta',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('paymentgeteway.update'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_gateways', [
            'id' => $bkash->id,
            'type' => 'bkash',
            'username' => 'new_bkash_user',
            'app_key' => 'new_app_key_xyz',
            'status' => 1,
        ]);
    }

    public function test_admin_can_update_shurjopay_gateway_settings(): void
    {
        $shurjopay = PaymentGateway::create([
            'type' => 'shurjopay',
            'username' => 'old_sp_user',
            'prefix' => 'OLD',
            'password' => 'old_pass',
            'base_url' => 'https://old.engine.com',
            'success_url' => 'https://old.com/success',
            'return_url' => 'https://old.com/return',
            'status' => 1,
        ]);

        $payload = [
            'id' => $shurjopay->id,
            'type' => 'shurjopay',
            'username' => 'new_sp_user',
            'prefix' => 'NEWSP',
            'password' => 'new_sp_pass',
            'base_url' => 'https://engine.shurjopayment.com',
            'success_url' => 'https://mondolshopbd.test/payment/success',
            'return_url' => 'https://mondolshopbd.test/payment/return',
            'status' => '0',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('paymentgeteway.update'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('payment_gateways', [
            'id' => $shurjopay->id,
            'type' => 'shurjopay',
            'username' => 'new_sp_user',
            'prefix' => 'NEWSP',
            'status' => 0,
        ]);
    }

    public function test_pay_update_validates_required_id(): void
    {
        $payload = [
            'id' => 99999,
            'username' => 'invalid_user',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('paymentgeteway.update'), $payload);

        $response->assertSessionHasErrors(['id']);
    }
}