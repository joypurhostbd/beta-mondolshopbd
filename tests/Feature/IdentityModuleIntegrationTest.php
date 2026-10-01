<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Application\Actions\AuthenticateCustomerAction;
use Modules\Customer\Application\Actions\ChangeCustomerPasswordAction;
use Modules\Customer\Application\Actions\LogoutCustomerAction;
use Modules\Customer\Application\Actions\RegisterCustomerAction;
use Modules\Customer\Application\Actions\RequestPasswordResetAction;
use Modules\Customer\Application\Actions\ResendPasswordResetOtpAction;
use Modules\Customer\Application\Actions\ResetCustomerPasswordAction;
use Modules\Customer\Application\Actions\UpdateCustomerProfileAction;
use Modules\Customer\Application\Actions\VerifyPasswordResetOtpAction;
use Modules\Customer\Domain\Contracts\CustomerRepositoryInterface;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Modules\Customer\Domain\Events\CustomerPasswordResetRequestedEvent;
use Modules\Customer\Domain\Events\CustomerRegisteredEvent;
use Shared\Domain\Contracts\Modules\CustomerModuleInterface;
use Tests\TestCase;

class IdentityModuleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private CustomerRepositoryInterface $repo;
    private CustomerModuleInterface $customerModule;
    private SmsServiceInterface $smsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->app->make(CustomerRepositoryInterface::class);
        $this->customerModule = $this->app->make(CustomerModuleInterface::class);
        $this->smsService = $this->app->make(SmsServiceInterface::class);
    }

    public function test_full_identity_and_customer_lifecycle_end_to_end(): void
    {
        Event::fake([
            CustomerRegisteredEvent::class,
            CustomerPasswordResetRequestedEvent::class,
        ]);

        // 1. Customer Registration
        $registerAction = new RegisterCustomerAction($this->repo);
        $customerDTO = $registerAction->execute([
            'name' => 'Kawsar Mahmud',
            'phone' => '01712345678',
            'email' => 'kawsar@mondolshopbd.com',
            'password' => 'initialSecurePass123',
            'balance' => 100,
        ]);

        $this->assertNotNull($customerDTO->id);
        $this->assertEquals('Kawsar Mahmud', $customerDTO->name);
        $this->assertEquals('01712345678', $customerDTO->phone);
        $this->assertEquals(100.0, $customerDTO->balance);
        Event::assertDispatched(CustomerRegisteredEvent::class);

        // 2. Cross-Module Contract Queries via CustomerModuleInterface
        $foundById = $this->customerModule->findCustomerById($customerDTO->id);
        $this->assertNotNull($foundById);
        $this->assertEquals('Kawsar Mahmud', $foundById['name']);

        $foundByPhone = $this->customerModule->findCustomerByPhone('01712345678');
        $this->assertNotNull($foundByPhone);
        $this->assertEquals($customerDTO->id, $foundByPhone['id']);

        // 3. Customer Authentication
        $authAction = new AuthenticateCustomerAction($this->repo);
        $loggedDTO = $authAction->execute('01712345678', 'initialSecurePass123');
        $this->assertNotNull($loggedDTO);
        $this->assertTrue(Auth::guard('customer')->check());
        $this->assertEquals($customerDTO->id, Auth::guard('customer')->id());

        // 4. Update Profile Info
        $updateAction = new UpdateCustomerProfileAction($this->repo);
        $updatedDTO = $updateAction->execute($customerDTO->id, [
            'name' => 'Kawsar Mahmud Chowdhury',
            'district' => 'Dhaka',
            'area' => 'Uttara',
            'address' => 'Sector 7, Road 4',
        ]);
        $this->assertEquals('Kawsar Mahmud Chowdhury', $updatedDTO->name);
        $this->assertEquals('Dhaka', $updatedDTO->district);

        // 5. Change Password Directly
        $changePasswordAction = new ChangeCustomerPasswordAction();
        $pwdChanged = $changePasswordAction->execute($customerDTO->id, 'initialSecurePass123', 'interimPass456');
        $this->assertTrue($pwdChanged);

        // 6. Customer Logout
        $logoutAction = new LogoutCustomerAction();
        $logoutAction->execute();
        $this->assertFalse(Auth::guard('customer')->check());

        // 7. Password Reset Request Flow (Forgot Password)
        $requestResetAction = new RequestPasswordResetAction($this->smsService);
        $resetCustomerId = $requestResetAction->execute('01712345678');
        $this->assertEquals($customerDTO->id, $resetCustomerId);

        $customerModel = Customer::find($customerDTO->id);
        $generatedOtp = $customerModel->forgot;
        $this->assertNotEmpty($generatedOtp);
        Event::assertDispatched(CustomerPasswordResetRequestedEvent::class);

        // 8. Verify Reset OTP
        $verifyOtpAction = new VerifyPasswordResetOtpAction();
        $this->assertTrue($verifyOtpAction->execute($customerDTO->id, $generatedOtp));
        $this->assertFalse($verifyOtpAction->execute($customerDTO->id, 999999));

        // 9. Reset Password & Invalidate OTP
        $resetPassAction = new ResetCustomerPasswordAction();
        $resetSuccess = $resetPassAction->execute($customerDTO->id, $generatedOtp, 'finalUltraSecurePassword789');
        $this->assertTrue($resetSuccess);

        $customerModel->refresh();
        $this->assertNull($customerModel->forgot, 'OTP token must be invalidated after password reset.');
        $this->assertTrue(Hash::check('finalUltraSecurePassword789', $customerModel->password));

        // 10. Re-Authenticate with New Password
        $reAuthDTO = $authAction->execute('01712345678', 'finalUltraSecurePassword789');
        $this->assertNotNull($reAuthDTO);
        $this->assertTrue(Auth::guard('customer')->check());

        // 11. Clean Logout
        $logoutAction->execute();
        $this->assertFalse(Auth::guard('customer')->check());
    }
}