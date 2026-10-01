<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFreshInstallWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_admin_can_login_and_access_dashboard_without_errors(): void
    {
        // 1. Run all database seeders
        $this->seed(DatabaseSeeder::class);

        // 2. Access admin login page
        $loginPageResponse = $this->get('/admin/login');
        $loginPageResponse->assertStatus(200);

        // 3. Post login credentials for seeded admin
        $loginResponse = $this->post('/admin/login', [
            'email' => 'admin@gmail.com',
            'password' => '123456',
        ]);

        $loginResponse->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();

        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Admin'));

        // 4. Access admin dashboard as authenticated admin
        $dashboardResponse = $this->actingAs($admin)->get('/admin/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard');
        $dashboardResponse->assertSee('Total Orders');
        $dashboardResponse->assertSee('Total Revenue');
        $dashboardResponse->assertSee('Delivery Performance');
        $dashboardResponse->assertSee($admin->name);
    }

    public function test_seeded_admin_can_access_core_management_pages_without_errors(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($admin);

        $coreRoutes = [
            route('admin.orders', 'all'),
            route('products.index'),
            route('categories.index'),
            route('banners.index'),
            route('orderstatus.index'),
            route('settings.index'),
        ];

        foreach ($coreRoutes as $url) {
            $response = $this->actingAs($admin)->get($url);
            $response->assertStatus(200);
        }
    }
}
