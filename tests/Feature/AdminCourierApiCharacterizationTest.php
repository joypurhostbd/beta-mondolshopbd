<?php

namespace Tests\Feature;

use App\Models\Courierapi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminCourierApiCharacterizationTest extends TestCase
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

    public function test_guest_cannot_access_courier_manage(): void
    {
        $response = $this->get(route('courierapi.manage'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_without_permissions_is_forbidden(): void
    {
        $plainUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($plainUser)->get(route('courierapi.manage'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_courier_manage_with_kpis(): void
    {
        Courierapi::create([
            'type' => 'steadfast',
            'api_key' => 'steadfast_key_123',
            'secret_key' => 'steadfast_secret_123',
            'url' => 'https://portal.steadfast.com.bd/api/v1',
            'status' => 1,
        ]);

        Courierapi::create([
            'type' => 'pathao',
            'url' => 'https://api-hermes.pathao.com',
            'token' => 'pathao_token_123',
            'status' => 1,
        ]);

        Courierapi::create([
            'type' => 'fraud',
            'token' => 'fraud_token_123',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('courierapi.manage'));

        $response->assertStatus(200);
        $response->assertSee('Courier');
        $response->assertSee('Steadfast Courier API');
        $response->assertSee('Pathao Courier API');
        $response->assertSee('Fraud Check API');
        $response->assertSee('Total APIs');
        $response->assertSee('Steadfast Status');
        $response->assertSee('Pathao Status');
        $response->assertSee('Fraud Check');
        $response->assertSee('steadfast_key_123');
        $response->assertSee('https://api-hermes.pathao.com');
    }

    public function test_admin_can_update_steadfast_settings(): void
    {
        $steadfast = Courierapi::create([
            'type' => 'steadfast',
            'api_key' => 'old_key',
            'secret_key' => 'old_secret',
            'url' => 'https://old.steadfast.com',
            'status' => 0,
        ]);

        $payload = [
            'id' => $steadfast->id,
            'type' => 'steadfast',
            'api_key' => 'new_steadfast_key',
            'secret_key' => 'new_steadfast_secret',
            'url' => 'https://portal.steadfast.com.bd/api/v1',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('courierapi.update'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('courierapis', [
            'id' => $steadfast->id,
            'type' => 'steadfast',
            'api_key' => 'new_steadfast_key',
            'secret_key' => 'new_steadfast_secret',
            'status' => 1,
        ]);
    }

    public function test_admin_can_update_pathao_settings(): void
    {
        $pathao = Courierapi::create([
            'type' => 'pathao',
            'url' => 'https://old.pathao.com',
            'token' => 'old_token',
            'status' => 0,
        ]);

        $payload = [
            'id' => $pathao->id,
            'type' => 'pathao',
            'url' => 'https://api-hermes.pathao.com',
            'token' => 'new_pathao_bearer_token',
            'status' => '1',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('courierapi.update'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('courierapis', [
            'id' => $pathao->id,
            'type' => 'pathao',
            'token' => 'new_pathao_bearer_token',
            'status' => 1,
        ]);
    }

    public function test_admin_can_update_fraud_check_settings(): void
    {
        $fraud = Courierapi::create([
            'type' => 'fraud',
            'token' => 'old_fraud_token',
            'status' => 1,
        ]);

        $payload = [
            'id' => $fraud->id,
            'type' => 'fraud',
            'token' => 'new_fraud_api_secret',
            'status' => '0',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('courierapi.update'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('courierapis', [
            'id' => $fraud->id,
            'type' => 'fraud',
            'token' => 'new_fraud_api_secret',
            'status' => 0,
        ]);
    }

    public function test_courier_update_validates_required_id(): void
    {
        $payload = [
            'id' => 99999,
            'api_key' => 'some_key',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('courierapi.update'), $payload);

        $response->assertSessionHasErrors(['id']);
    }
}