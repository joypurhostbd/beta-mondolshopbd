<?php

namespace Tests\Unit;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Modules\Customer\Application\Actions\AuthenticateCustomerAction;
use Modules\Customer\Application\Actions\ChangeCustomerPasswordAction;
use Modules\Customer\Application\Actions\LogoutCustomerAction;
use Modules\Customer\Application\Actions\RegisterCustomerAction;
use Modules\Customer\Application\Actions\UpdateCustomerProfileAction;
use Modules\Customer\Domain\Contracts\CustomerRepositoryInterface;
use Modules\Customer\Domain\Events\CustomerRegisteredEvent;
use Tests\TestCase;

class CustomerAuthActionsTest extends TestCase
{
    use RefreshDatabase;

    private CustomerRepositoryInterface $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->app->make(CustomerRepositoryInterface::class);
    }

    public function test_register_customer_action_creates_and_fires_event(): void
    {
        Event::fake([CustomerRegisteredEvent::class]);

        $action = new RegisterCustomerAction($this->repo);
        $dto = $action->execute([
            'name' => 'Abir Hossain',
            'phone' => '01819876543',
            'email' => 'abir@example.com',
            'password' => 'secretpass',
        ]);

        $this->assertEquals('Abir Hossain', $dto->name);
        $this->assertEquals('01819876543', $dto->phone);
        $this->assertDatabaseHas('customers', ['phone' => '01819876543']);

        Event::assertDispatched(CustomerRegisteredEvent::class);
    }

    public function test_register_customer_fails_on_duplicate_phone(): void
    {
        Customer::create([
            'name' => 'Existing User',
            'slug' => 'existing-user-01711223344',
            'phone' => '01711223344',
            'password' => Hash::make('password123'),
            'status' => 1,
        ]);

        $this->expectException(InvalidArgumentException::class);

        $action = new RegisterCustomerAction($this->repo);
        $action->execute([
            'name' => 'Duplicate User',
            'phone' => '01711223344',
            'password' => 'password123',
        ]);
    }

    public function test_authenticate_customer_action_logs_in_user(): void
    {
        $cust = Customer::create([
            'name' => 'Mahmudul Hasan',
            'slug' => 'mahmudul-hasan-01611223344',
            'phone' => '01611223344',
            'email' => 'mahmud@example.com',
            'password' => Hash::make('mypassword123'),
            'status' => 1,
            'verify' => 1,
        ]);

        $authAction = new AuthenticateCustomerAction($this->repo);

        // Success
        $dto = $authAction->execute('01611223344', 'mypassword123');
        $this->assertNotNull($dto);
        $this->assertEquals('Mahmudul Hasan', $dto->name);
        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertEquals($cust->id, Auth::guard('customer')->id());

        // Logout
        $logoutAction = new LogoutCustomerAction();
        $logoutAction->execute();
        $this->assertFalse(Auth::guard('customer')->check());

        // Failed password
        $failDto = $authAction->execute('01611223344', 'wrongpass');
        $this->assertNull($failDto);
    }

    public function test_update_profile_and_change_password_actions(): void
    {
        $cust = Customer::create([
            'name' => 'Nayeem Islam',
            'slug' => 'nayeem-islam-01511223344',
            'phone' => '01511223344',
            'password' => Hash::make('oldpass123'),
            'status' => 1,
        ]);

        $profileAction = new UpdateCustomerProfileAction($this->repo);
        $updatedDto = $profileAction->execute($cust->id, [
            'name' => 'Nayeem Islam Updated',
            'district' => 'Rajshahi',
            'address' => 'Shaheb Bazar',
        ]);

        $this->assertEquals('Nayeem Islam Updated', $updatedDto->name);
        $this->assertEquals('Rajshahi', $updatedDto->district);

        $passwordAction = new ChangeCustomerPasswordAction();
        $changed = $passwordAction->execute($cust->id, 'oldpass123', 'newsecurepass456');
        $this->assertTrue($changed);
        $this->assertTrue(Hash::check('newsecurepass456', $cust->fresh()->password));
    }
}