<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\IpBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminCustomerCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['status' => 1]);

        $permissions = [
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->adminUser->givePermissionTo($permissions);
    }

    public function test_admin_can_view_customer_manage_page_with_kpis(): void
    {
        Customer::create([
            'name' => 'John Customer',
            'slug' => 'john-customer',
            'phone' => '01711111111',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        Customer::create([
            'name' => 'Jane Inactive',
            'slug' => 'jane-inactive',
            'phone' => '01722222222',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('customers.index'));

        $response->assertStatus(200);
        $response->assertViewHas('show_data');
        $response->assertViewHas('kpis', function ($kpis) {
            return $kpis['total_customers'] === 2
                && $kpis['active_customers'] === 1
                && $kpis['inactive_customers'] === 1;
        });

        $response->assertSee('John Customer');
        $response->assertSee('01711111111');
        $response->assertSee('Jane Inactive');
    }

    public function test_admin_can_search_customers_by_keyword_and_filter_status(): void
    {
        Customer::create([
            'name' => 'Rahim Chowdhury',
            'slug' => 'rahim-chowdhury',
            'phone' => '01711000001',
            'email' => 'rahim@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        Customer::create([
            'name' => 'Karim Hasan',
            'slug' => 'karim-hasan',
            'phone' => '01822000002',
            'email' => 'karim@example.com',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        $keywordResponse = $this->actingAs($this->adminUser)->get(route('customers.index', ['keyword' => '01711000001']));
        $keywordResponse->assertStatus(200);
        $keywordResponse->assertSee('Rahim Chowdhury');
        $keywordResponse->assertDontSee('Karim Hasan');

        $statusResponse = $this->actingAs($this->adminUser)->get(route('customers.index', ['status' => 'inactive']));
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Karim Hasan');
        $statusResponse->assertDontSee('Rahim Chowdhury');
    }

    public function test_admin_can_view_edit_customer_page(): void
    {
        $customer = Customer::create([
            'name' => 'Alice Walker',
            'slug' => 'alice-walker',
            'phone' => '01733333333',
            'email' => 'alice@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('customers.edit', $customer->id));

        $response->assertStatus(200);
        $response->assertViewHas('edit_data');
        $response->assertSee('Alice Walker');
        $response->assertSee('01733333333');
    }

    public function test_admin_can_update_customer_details(): void
    {
        $customer = Customer::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'phone' => '01744444444',
            'email' => 'old@example.com',
            'password' => Hash::make('oldpass123'),
            'status' => 'active',
        ]);

        $avatar = UploadedFile::fake()->image('customer.jpg', 200, 200);

        $response = $this->actingAs($this->adminUser)->post(route('customers.update'), [
            'hidden_id' => $customer->id,
            'name' => 'New Name',
            'phone' => '01755555555',
            'email' => 'new@example.com',
            'password' => 'newsecret123',
            'address' => 'Dhaka, Bangladesh',
            'image' => $avatar,
            'status' => 1,
        ]);

        $response->assertRedirect(route('customers.index'));

        $customer->refresh();
        $this->assertEquals('New Name', $customer->name);
        $this->assertEquals('01755555555', $customer->phone);
        $this->assertEquals('new@example.com', $customer->email);
        $this->assertEquals('Dhaka, Bangladesh', $customer->address);
        $this->assertTrue(Hash::check('newsecret123', $customer->password));
    }

    public function test_admin_can_toggle_customer_status(): void
    {
        $customer = Customer::create([
            'name' => 'Status Test',
            'slug' => 'status-test',
            'phone' => '01766666666',
            'email' => 'statustest@example.com',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);

        $inactiveResponse = $this->actingAs($this->adminUser)->post(route('customers.inactive'), [
            'hidden_id' => $customer->id,
        ]);
        $inactiveResponse->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => 0,
        ]);

        $activeResponse = $this->actingAs($this->adminUser)->post(route('customers.active'), [
            'hidden_id' => $customer->id,
        ]);
        $activeResponse->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'status' => 1,
        ]);
    }

    public function test_admin_can_view_customer_profile(): void
    {
        $customer = Customer::create([
            'name' => 'Profile Customer',
            'slug' => 'profile-customer',
            'phone' => '01777777777',
            'email' => 'profile@example.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('customers.profile', ['id' => $customer->id]));

        $response->assertStatus(200);
        $response->assertViewHas('profile');
        $response->assertViewHas('customer_stats');
        $response->assertSee('Profile Customer');
        $response->assertSee('01777777777');
    }

    public function test_admin_can_manage_ip_blocks(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('customers.ip_block'));
        $response->assertStatus(200);
        $response->assertSee('IP Block Manage');

        $storeResponse = $this->actingAs($this->adminUser)->post(route('customers.ipblock.store'), [
            'ip_no' => '103.205.180.1',
            'reason' => 'Fraudulent spam orders',
        ]);
        $storeResponse->assertRedirect();
        $this->assertDatabaseHas('ip_blocks', [
            'ip_no' => '103.205.180.1',
            'reason' => 'Fraudulent spam orders',
        ]);

        $ipBlock = IpBlock::where('ip_no', '103.205.180.1')->first();
        $this->assertNotNull($ipBlock);

        $updateResponse = $this->actingAs($this->adminUser)->post(route('customers.ipblock.update'), [
            'id' => $ipBlock->id,
            'ip_no' => '103.205.180.2',
            'reason' => 'Updated fraud reason',
        ]);
        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('ip_blocks', [
            'id' => $ipBlock->id,
            'ip_no' => '103.205.180.2',
            'reason' => 'Updated fraud reason',
        ]);

        $destroyResponse = $this->actingAs($this->adminUser)->post(route('customers.ipblock.destroy'), [
            'id' => $ipBlock->id,
        ]);
        $destroyResponse->assertRedirect();
        $this->assertDatabaseMissing('ip_blocks', [
            'id' => $ipBlock->id,
        ]);
    }
}
