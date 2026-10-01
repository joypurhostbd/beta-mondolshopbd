<?php

namespace Tests\Feature;

use App\Models\OrderStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminOrderStatusCharacterizationTest extends TestCase
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

    public function test_guest_cannot_access_order_status_manage_or_create(): void
    {
        $responseManage = $this->get(route('orderstatus.index'));
        $responseManage->assertRedirect('/admin/login');

        $responseCreate = $this->get(route('orderstatus.create'));
        $responseCreate->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_order_status_index_with_kpis(): void
    {
        OrderStatus::create([
            'name' => 'Processing Stage',
            'slug' => 'processing-stage',
            'status' => 1,
        ]);

        OrderStatus::create([
            'name' => 'Archived Void',
            'slug' => 'archived-void',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('orderstatus.index'));

        $response->assertStatus(200);
        $response->assertViewHas('data');
        $response->assertViewHas('total_statuses', 2);
        $response->assertViewHas('active_statuses', 1);
        $response->assertViewHas('inactive_statuses', 1);
        $response->assertSee('Processing Stage');
        $response->assertSee('Archived Void');
    }

    public function test_admin_can_view_create_order_status(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('orderstatus.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Order Status');
        $response->assertSee('Status Name');
        $response->assertSee('Manage Statuses');
        $response->assertSee(route('orderstatus.index'));
    }

    public function test_admin_can_store_order_status_active_and_inactive(): void
    {
        // Store Active Status
        $response = $this->actingAs($this->adminUser)->post(route('orderstatus.store'), [
            'name' => 'Ready for Pickup',
            'status' => 1,
        ]);

        $response->assertRedirect(route('orderstatus.index'));

        $this->assertDatabaseHas('order_statuses', [
            'name' => 'Ready for Pickup',
            'slug' => 'ready-for-pickup',
            'status' => 1,
        ]);

        // Store Inactive Status (no status checkbox)
        $responseInactive = $this->actingAs($this->adminUser)->post(route('orderstatus.store'), [
            'name' => 'Failed Delivery',
        ]);

        $responseInactive->assertRedirect(route('orderstatus.index'));

        $this->assertDatabaseHas('order_statuses', [
            'name' => 'Failed Delivery',
            'slug' => 'failed-delivery',
            'status' => 0,
        ]);
    }

    public function test_admin_can_view_edit_and_update_order_status(): void
    {
        $status = OrderStatus::create([
            'name' => 'Out for Courier',
            'slug' => 'out-for-courier',
            'status' => 0,
        ]);

        $editResponse = $this->actingAs($this->adminUser)->get(route('orderstatus.edit', $status->id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Out for Courier');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('orderstatus.update'), [
            'id' => $status->id,
            'hidden_id' => $status->id,
            'name' => 'Handed over to Rider',
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('orderstatus.index'));

        $this->assertDatabaseHas('order_statuses', [
            'id' => $status->id,
            'name' => 'Handed over to Rider',
            'slug' => 'handed-over-to-rider',
            'status' => 1,
        ]);
    }

    public function test_admin_can_toggle_order_status(): void
    {
        $status = OrderStatus::create([
            'name' => 'On Hold',
            'slug' => 'on-hold',
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('orderstatus.inactive'), [
            'hidden_id' => $status->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('order_statuses', ['id' => $status->id, 'status' => 0]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('orderstatus.active'), [
            'hidden_id' => $status->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('order_statuses', ['id' => $status->id, 'status' => 1]);
    }

    public function test_admin_can_destroy_order_status(): void
    {
        $status = OrderStatus::create([
            'name' => 'Temporary Status',
            'slug' => 'temp-status',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('orderstatus.destroy'), [
            'hidden_id' => $status->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('order_statuses', ['id' => $status->id]);
    }
}