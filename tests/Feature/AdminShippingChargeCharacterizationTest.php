<?php

namespace Tests\Feature;

use App\Models\ShippingCharge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminShippingChargeCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'shipping-list',
            'shipping-create',
            'shipping-edit',
            'shipping-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_guest_cannot_access_shipping_charge_manage_or_create(): void
    {
        $responseManage = $this->get(route('shippingcharges.index'));
        $responseManage->assertRedirect('/admin/login');

        $responseCreate = $this->get(route('shippingcharges.create'));
        $responseCreate->assertRedirect('/admin/login');
    }

    public function test_admin_can_view_shipping_charge_index_with_kpis(): void
    {
        ShippingCharge::create([
            'name' => 'Inside Dhaka',
            'amount' => 60.00,
            'status' => 1,
        ]);

        ShippingCharge::create([
            'name' => 'Outside Dhaka',
            'amount' => 120.00,
            'status' => 1,
        ]);

        ShippingCharge::create([
            'name' => 'Special Express Zone',
            'amount' => 200.00,
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('shippingcharges.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('total_rates', 3);
        $response->assertViewHas('active_rates', 2);
        $response->assertViewHas('inactive_rates', 1);
        $response->assertViewHas('avg_rate', 90.0);
        $response->assertSee('Inside Dhaka');
        $response->assertSee('Outside Dhaka');
        $response->assertSee('60.00');
        $response->assertSee('120.00');
    }

    public function test_admin_can_view_create_shipping_charge(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('shippingcharges.create'));

        $response->assertStatus(200);
        $response->assertSee('Add Shipping Charge');
        $response->assertSee('Delivery Charge Amount');
    }

    public function test_admin_can_store_shipping_charge_active_and_inactive(): void
    {
        // Store Active Charge
        $response = $this->actingAs($this->adminUser)->post(route('shippingcharges.store'), [
            'name' => 'Chittagong City',
            'amount' => 80.00,
            'status' => 1,
        ]);

        $response->assertRedirect(route('shippingcharges.index'));

        $this->assertDatabaseHas('shipping_charges', [
            'name' => 'Chittagong City',
            'amount' => 80.00,
            'status' => 1,
        ]);

        // Store Inactive Charge (no status checkbox in request)
        $responseInactive = $this->actingAs($this->adminUser)->post(route('shippingcharges.store'), [
            'name' => 'Sylhet Metro',
            'amount' => 100.00,
        ]);

        $responseInactive->assertRedirect(route('shippingcharges.index'));

        $this->assertDatabaseHas('shipping_charges', [
            'name' => 'Sylhet Metro',
            'amount' => 100.00,
            'status' => 0,
        ]);
    }

    public function test_admin_can_view_edit_and_update_shipping_charge(): void
    {
        $charge = ShippingCharge::create([
            'name' => 'Old Dhaka Suburban',
            'amount' => 70.00,
            'status' => 0,
        ]);

        $editResponse = $this->actingAs($this->adminUser)->get(route('shippingcharges.edit', $charge->id));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Old Dhaka Suburban');

        $updateResponse = $this->actingAs($this->adminUser)->post(route('shippingcharges.update'), [
            'id' => $charge->id,
            'hidden_id' => $charge->id,
            'name' => 'Dhaka Suburban Area',
            'amount' => 75.00,
            'status' => 1,
        ]);

        $updateResponse->assertRedirect(route('shippingcharges.index'));

        $this->assertDatabaseHas('shipping_charges', [
            'id' => $charge->id,
            'name' => 'Dhaka Suburban Area',
            'amount' => 75.00,
            'status' => 1,
        ]);
    }

    public function test_admin_can_toggle_shipping_charge_status(): void
    {
        $charge = ShippingCharge::create([
            'name' => 'Rajshahi Zone',
            'amount' => 110.00,
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('shippingcharges.inactive'), [
            'hidden_id' => $charge->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('shipping_charges', ['id' => $charge->id, 'status' => 0]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('shippingcharges.active'), [
            'hidden_id' => $charge->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('shipping_charges', ['id' => $charge->id, 'status' => 1]);
    }

    public function test_admin_can_destroy_shipping_charge(): void
    {
        $charge = ShippingCharge::create([
            'name' => 'Temporary Promo Rate',
            'amount' => 0.00,
            'status' => 0,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('shippingcharges.destroy'), [
            'hidden_id' => $charge->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('shipping_charges', ['id' => $charge->id]);
    }
}