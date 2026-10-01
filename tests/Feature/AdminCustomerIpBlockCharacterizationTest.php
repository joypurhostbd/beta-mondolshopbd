<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\IpBlock;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminCustomerIpBlockCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'customer-list',
            'customer-edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_guest_cannot_access_ip_block_manage(): void
    {
        $response = $this->get(route('customers.ip_block'));
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_without_permissions_is_forbidden(): void
    {
        $plainUser = User::factory()->create(['status' => 1]);

        $response = $this->actingAs($plainUser)->get(route('customers.ip_block'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_ip_block_page_with_kpis(): void
    {
        IpBlock::create([
            'ip_no' => '103.230.104.55',
            'reason' => 'Fraudulent fake order attempts',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('customers.ip_block'));

        $response->assertStatus(200);
        $response->assertSee('Security & IP Access Control', false);
        $response->assertSee('Blocked IPs');
        $response->assertSee('Recent Blocks (7d)');
        $response->assertSee('Total Customers');
        $response->assertSee('Total Orders');
        $response->assertSee('103.230.104.55');
        $response->assertSee('Fraudulent fake order attempts');
    }

    public function test_admin_can_store_ip_block(): void
    {
        $payload = [
            'ip_no' => '182.160.100.22',
            'reason' => 'Brute force login spammer',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('customers.ipblock.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('ip_blocks', [
            'ip_no' => '182.160.100.22',
            'reason' => 'Brute force login spammer',
        ]);
    }

    public function test_store_validation_requires_ip_and_reason(): void
    {
        $payload = [
            'ip_no' => '',
            'reason' => '',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('customers.ipblock.store'), $payload);

        $response->assertSessionHasErrors(['ip_no', 'reason']);
    }

    public function test_admin_can_update_blocked_ip(): void
    {
        $ip = IpBlock::create([
            'ip_no' => '192.168.1.50',
            'reason' => 'Suspicious traffic',
        ]);

        $payload = [
            'id' => $ip->id,
            'ip_no' => '192.168.1.55',
            'reason' => 'Confirmed malicious scanner',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('customers.ipblock.update'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('ip_blocks', [
            'id' => $ip->id,
            'ip_no' => '192.168.1.55',
            'reason' => 'Confirmed malicious scanner',
        ]);
    }

    public function test_admin_can_destroy_blocked_ip(): void
    {
        $ip = IpBlock::create([
            'ip_no' => '10.0.0.99',
            'reason' => 'Temporary test block',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('customers.ipblock.destroy'), [
            'id' => $ip->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('ip_blocks', [
            'id' => $ip->id,
        ]);
    }

    public function test_ip_filter_middleware_blocks_blacklisted_ip(): void
    {
        IpBlock::create([
            'ip_no' => '198.51.100.77',
            'reason' => 'Automated bot activity',
        ]);

        // Accessing route with 'ipcheck' middleware from the blocked IP
        $response = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.77'])
            ->get('/');

        $response->assertStatus(403);
        $response->assertSee('Automated bot activity');
    }
}