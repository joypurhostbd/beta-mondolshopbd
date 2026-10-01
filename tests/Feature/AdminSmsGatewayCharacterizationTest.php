<?php

namespace Tests\Feature;

use App\Models\SmsGateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminSmsGatewayCharacterizationTest extends TestCase
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

    public function test_guest_cannot_access_sms_gateway_manage(): void
    {
        $response = $this->get(route('smsgeteway.manage'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_without_permissions_is_forbidden(): void
    {
        $plainUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($plainUser)->get(route('smsgeteway.manage'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_sms_gateway_manage_with_kpis(): void
    {
        SmsGateway::create([
            'url' => 'http://bulksmsbd.net/api/smsapi',
            'api_key' => 'secret_api_key_123',
            'serderid' => '8809612000000',
            'status' => 1,
            'order' => 1,
            'forget_pass' => 1,
            'password_g' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('smsgeteway.manage'));

        $response->assertStatus(200);
        $response->assertSee('SMS Gateway Configuration');
        $response->assertSee('Master SMS');
        $response->assertSee('Order Alert');
        $response->assertSee('OTP / Forgot Pass');
        $response->assertSee('Auto-Password');
        $response->assertSee('http://bulksmsbd.net/api/smsapi');
        $response->assertSee('8809612000000');
    }

    public function test_admin_can_update_sms_gateway_settings(): void
    {
        $sms = SmsGateway::create([
            'url' => 'http://old.sms.com',
            'api_key' => 'old_key',
            'serderid' => 'old_sender',
            'status' => 0,
            'order' => 0,
            'forget_pass' => 0,
            'password_g' => 0,
        ]);

        $payload = [
            'id' => $sms->id,
            'url' => 'http://bulksmsbd.net/api/smsapi',
            'api_key' => 'new_secret_key_456',
            'sender_id' => '8809612999999',
            'status' => '1',
            'order' => '1',
            'forget_pass' => '1',
            'password_g' => '1',
            'admin_phone' => '01711223344, 01811223344',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('smsgeteway.update'), $payload);

        $response->assertRedirect(route('smsgeteway.manage'));
        $this->assertDatabaseHas('sms_gateways', [
            'id' => $sms->id,
            'url' => 'http://bulksmsbd.net/api/smsapi',
            'api_key' => 'new_secret_key_456',
            'serderid' => '8809612999999',
            'status' => 1,
            'order' => 1,
            'forget_pass' => 1,
            'password_g' => 1,
            'admin_phone' => '01711223344, 01811223344',
        ]);
    }

    public function test_sms_update_validates_required_id(): void
    {
        $payload = [
            'id' => 99999,
            'url' => 'http://example.com',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('smsgeteway.update'), $payload);

        $response->assertSessionHasErrors(['id']);
    }

    public function test_get_sms_gateway_save_redirects_to_manage_screen(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/smsgeteway/save');

        $response->assertRedirect(route('smsgeteway.manage'));
    }

    public function test_guest_cannot_access_sms_gateway_save(): void
    {
        $response = $this->get('/admin/smsgeteway/save');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_update_sms_gateway_with_joypurhost_provider(): void
    {
        $sms = SmsGateway::create([
            'url' => 'http://bulksmsbd.net/api/smsapi',
            'api_key' => 'jh_key',
            'serderid' => '8809612000000',
            'provider' => 'custom',
            'status' => 1,
            'order' => 1,
            'forget_pass' => 1,
            'password_g' => 0,
        ]);

        $payload = [
            'id' => $sms->id,
            'provider' => 'joypurhost',
            'url' => 'http://bulksmsbd.net/api/smsapi',
            'api_key' => 'jh_secret_key',
            'sender_id' => '8809612000000',
            'status' => '1',
            'order' => '1',
            'forget_pass' => '1',
            'password_g' => '0',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('smsgeteway.update'), $payload);

        $response->assertRedirect(route('smsgeteway.manage'));
        $this->assertDatabaseHas('sms_gateways', [
            'id' => $sms->id,
            'provider' => 'joypurhost',
            'api_key' => 'jh_secret_key',
        ]);
    }

    public function test_admin_can_update_sms_gateway_with_custom_provider(): void
    {
        $sms = SmsGateway::create([
            'url' => 'http://bulksmsbd.net/api/smsapi',
            'api_key' => 'custom_key',
            'serderid' => '8809612000000',
            'provider' => 'joypurhost',
            'status' => 1,
            'order' => 1,
            'forget_pass' => 1,
            'password_g' => 0,
        ]);

        $payload = [
            'id' => $sms->id,
            'provider' => 'custom',
            'url' => 'https://custom-sms.example.com/send',
            'api_key' => 'custom_secret_key',
            'sender_id' => 'MyCustomBrand',
            'status' => '1',
            'order' => '1',
            'forget_pass' => '1',
            'password_g' => '0',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('smsgeteway.update'), $payload);

        $response->assertRedirect(route('smsgeteway.manage'));
        $this->assertDatabaseHas('sms_gateways', [
            'id' => $sms->id,
            'provider' => 'custom',
            'url' => 'https://custom-sms.example.com/send',
            'serderid' => 'MyCustomBrand',
        ]);
    }

    public function test_sms_update_rejects_invalid_provider(): void
    {
        $sms = SmsGateway::create([
            'url' => 'http://example.com',
            'provider' => 'joypurhost',
            'status' => 1,
        ]);

        $payload = [
            'id' => $sms->id,
            'provider' => 'unsupported_provider',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('smsgeteway.update'), $payload);

        $response->assertSessionHasErrors(['provider']);
    }

    public function test_admin_can_test_joypurhost_sms_connection(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://sms.joypurhost.com/api/getBalanceApi*' => \Illuminate\Support\Facades\Http::response([
                'response_code' => 202,
                'balance' => 375.49,
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('smsgeteway.test'), [
            'provider' => 'joypurhost',
            'api_key' => 'valid_api_key',
            'sender_id' => '8809612000000',
            'url' => 'https://sms.joypurhost.com/api/smsapi',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'current_balance' => '375.49',
        ]);
    }

    public function test_admin_can_send_test_sms(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://sms.joypurhost.com/api/smsapi*' => \Illuminate\Support\Facades\Http::response([
                'response_code' => 202,
                'success_message' => 'SMS Submitted Successfully',
            ], 200),
        ]);

        $response = $this->actingAs($this->adminUser)->postJson(route('smsgeteway.send_test'), [
            'phone' => '01712345678',
            'message' => 'Test message',
            'provider' => 'joypurhost',
            'api_key' => 'valid_api_key',
            'sender_id' => '8809612000000',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_guest_cannot_test_sms_connection(): void
    {
        $response = $this->postJson(route('smsgeteway.test'), [
            'provider' => 'joypurhost',
            'api_key' => 'valid_api_key',
        ]);

        $response->assertUnauthorized();
    }

    public function test_admin_can_switch_and_persist_joypurhost_and_custom_providers_independently(): void
    {
        // 1. Setup JoypurHost provider
        $jh = \App\Models\SmsProviderJoypurhost::getOrCreate();
        $jhPayload = [
            'id' => $jh->id,
            'provider' => 'joypurhost',
            'url' => 'https://sms.joypurhost.com/api/smsapi',
            'api_key' => 'jh_master_secret',
            'sender_id' => 'JoypurBrand',
            'status' => '1',
        ];
        $this->actingAs($this->adminUser)->post(route('smsgeteway.update'), $jhPayload);

        $this->assertDatabaseHas('sms_gateways', [
            'id' => $jh->id,
            'provider' => 'joypurhost',
            'api_key' => 'jh_master_secret',
            'status' => 1,
        ]);

        // 2. Setup Custom provider
        $custom = \App\Models\SmsGateway::custom();
        $customPayload = [
            'id' => $custom->id,
            'provider' => 'custom',
            'url' => 'https://my-custom-sms.com/send',
            'api_key' => 'custom_token_xyz',
            'sender_id' => 'CustomBrand',
            'status' => '1', // activating custom
        ];
        $this->actingAs($this->adminUser)->post(route('smsgeteway.update'), $customPayload);

        // 3. Verify Custom is active and has custom credentials
        $this->assertDatabaseHas('sms_gateways', [
            'id' => $custom->id,
            'provider' => 'custom',
            'api_key' => 'custom_token_xyz',
            'status' => 1,
        ]);

        // 4. Verify JoypurHost was deactivated but its credentials were NOT overwritten
        $this->assertDatabaseHas('sms_gateways', [
            'id' => $jh->id,
            'provider' => 'joypurhost',
            'api_key' => 'jh_master_secret',
            'status' => 0,
        ]);
    }
}