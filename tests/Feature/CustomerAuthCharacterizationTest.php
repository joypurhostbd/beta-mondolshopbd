<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(array $attributes = []): Customer
    {
        $customer = new Customer();
        $customer->name = $attributes['name'] ?? 'Test Customer';
        $customer->slug = $attributes['slug'] ?? 'test-customer';
        $customer->phone = $attributes['phone'] ?? '01799999999';
        $customer->email = $attributes['email'] ?? 'test@example.com';
        $customer->password = $attributes['password'] ?? Hash::make('password123');
        $customer->verify = $attributes['verify'] ?? 1;
        $customer->status = $attributes['status'] ?? 1;
        if (isset($attributes['forgot'])) {
            $customer->forgot = $attributes['forgot'];
        }
        $customer->save();

        return $customer;
    }

    public function test_customer_can_view_registration_page()
    {
        $response = $this->get(route('customer.register'));

        $response->assertStatus(200);
        $response->assertViewIs('frontEnd.layouts.customer.register');
    }

    public function test_customer_registration_creates_account_and_redirects_to_login()
    {
        $payload = [
            'name' => 'Sizar Babu',
            'phone' => '01712345678',
            'email' => 'sizar@example.com',
            'password' => 'password123',
        ];

        $response = $this->post(route('customer.store'), $payload);

        $response->assertRedirect(route('customer.login'));
        $this->assertDatabaseHas('customers', [
            'name' => 'Sizar Babu',
            'phone' => '01712345678',
            'email' => 'sizar@example.com',
            'verify' => 1,
            'status' => 1,
        ]);
    }

    public function test_customer_registration_fails_on_duplicate_phone()
    {
        $this->createCustomer([
            'name' => 'Existing Customer',
            'slug' => 'existing-customer',
            'phone' => '01712345678',
            'password' => Hash::make('secret123'),
        ]);

        $payload = [
            'name' => 'Another Customer',
            'phone' => '01712345678',
            'password' => 'password123',
        ];

        $response = $this->from(route('customer.register'))->post(route('customer.store'), $payload);

        $response->assertSessionHasErrors(['phone']);
        $response->assertRedirect(route('customer.register'));
    }

    public function test_customer_can_view_login_page()
    {
        $response = $this->get(route('customer.login'));

        $response->assertStatus(200);
        $response->assertViewIs('frontEnd.layouts.customer.login');
    }

    public function test_customer_can_login_with_valid_credentials()
    {
        $customer = $this->createCustomer([
            'name' => 'Test Customer',
            'slug' => 'test-customer',
            'phone' => '01799999999',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('customer.signin'), [
            'phone' => '01799999999',
            'password' => 'password123',
        ]);

        $response->assertRedirect('customer/account');
        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertEquals($customer->id, Auth::guard('customer')->id());
    }

    public function test_customer_login_fails_with_invalid_credentials()
    {
        $this->createCustomer([
            'name' => 'Test Customer',
            'slug' => 'test-customer',
            'phone' => '01799999999',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->from(route('customer.login'))->post(route('customer.signin'), [
            'phone' => '01799999999',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect(route('customer.login'));
        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_customer_can_logout()
    {
        $customer = $this->createCustomer([
            'name' => 'Test Customer',
            'slug' => 'test-customer',
            'phone' => '01799999999',
            'password' => Hash::make('password123'),
        ]);

        Auth::guard('customer')->login($customer);
        $this->assertTrue(Auth::guard('customer')->check());

        $response = $this->post(route('customer.logout'));

        $response->assertRedirect(route('customer.login'));
        $this->assertFalse(Auth::guard('customer')->check());
    }

    public function test_forgot_password_generates_otp_and_session()
    {
        $customer = $this->createCustomer([
            'name' => 'Forgot Customer',
            'slug' => 'forgot-customer',
            'phone' => '01788888888',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('customer.forgot.verify'), [
            'phone' => '01788888888',
        ]);

        $response->assertRedirect(route('customer.forgot.reset'));
        $response->assertSessionHas('verify_phone', '01788888888');

        $customer->refresh();
        $this->assertNotNull($customer->forgot);
        $this->assertGreaterThanOrEqual(1000, $customer->forgot);
    }

    public function test_forgot_password_resets_password_with_valid_otp()
    {
        $customer = $this->createCustomer([
            'name' => 'Forgot Customer',
            'slug' => 'forgot-customer',
            'phone' => '01788888888',
            'password' => Hash::make('oldpassword'),
            'forgot' => 4567,
        ]);

        $response = $this->withSession(['verify_phone' => '01788888888'])
            ->post(route('customer.forgot.store'), [
                'otp' => 4567,
                'password' => 'newpassword123',
            ]);

        $response->assertRedirect('customer/account');
        $this->assertTrue(Auth::guard('customer')->check());

        $customer->refresh();
        $this->assertTrue(Hash::check('newpassword123', $customer->password));
        $this->assertEquals(1, $customer->forgot);
    }
}
