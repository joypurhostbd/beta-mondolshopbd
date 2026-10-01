<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_renders_successfully()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Enter your email address and password to access admin panel');
        $response->assertSee('Log In');
    }

    public function test_public_login_url_redirects_to_customer_login_for_privacy()
    {
        $response = $this->get('/login');

        $response->assertRedirect(route('customer.login'));
    }

    public function test_unauthenticated_admin_access_redirects_to_admin_login()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@mondolshopbd.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@mondolshopbd.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@mondolshopbd.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@mondolshopbd.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
