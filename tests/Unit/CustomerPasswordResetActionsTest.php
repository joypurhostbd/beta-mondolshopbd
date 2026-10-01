<?php

namespace Tests\Unit;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Modules\Customer\Application\Actions\RequestPasswordResetAction;
use Modules\Customer\Application\Actions\ResendPasswordResetOtpAction;
use Modules\Customer\Application\Actions\ResetCustomerPasswordAction;
use Modules\Customer\Application\Actions\VerifyPasswordResetOtpAction;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Modules\Customer\Domain\Events\CustomerPasswordResetRequestedEvent;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Tests\TestCase;

class CustomerPasswordResetActionsTest extends TestCase
{
    use RefreshDatabase;

    private SmsServiceInterface $smsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smsService = $this->app->make(SmsServiceInterface::class);
    }

    public function test_request_password_reset_generates_otp_and_dispatches_event(): void
    {
        Event::fake([CustomerPasswordResetRequestedEvent::class]);

        $customer = Customer::create([
            'name' => 'Jannat Ara',
            'slug' => 'jannat-ara-01722334455',
            'phone' => '01722334455',
            'password' => Hash::make('oldpassword'),
            'status' => 1,
        ]);

        $action = new RequestPasswordResetAction($this->smsService);
        $customerId = $action->execute('01722334455');

        $this->assertEquals($customer->id, $customerId);

        $customer->refresh();
        $this->assertNotEmpty($customer->forgot);
        $this->assertIsInt((int) $customer->forgot);

        Event::assertDispatched(CustomerPasswordResetRequestedEvent::class, function ($event) use ($customer) {
            return $event->customerId === $customer->id &&
                $event->phone === '01722334455' &&
                $event->otp === (int) $customer->forgot;
        });
    }

    public function test_request_password_reset_throws_exception_for_unknown_phone(): void
    {
        $this->expectException(EntityNotFoundException::class);

        $action = new RequestPasswordResetAction($this->smsService);
        $action->execute('01799887766');
    }

    public function test_verify_password_reset_otp(): void
    {
        $customer = Customer::create([
            'name' => 'Shaon Ahmed',
            'slug' => 'shaon-ahmed-01822334455',
            'phone' => '01822334455',
            'password' => Hash::make('oldpass'),
            'status' => 1,
            'forgot' => 654321,
        ]);

        $verifyAction = new VerifyPasswordResetOtpAction();

        $this->assertTrue($verifyAction->execute($customer->id, 654321));
        $this->assertTrue($verifyAction->execute($customer->id, '654321'));
        $this->assertFalse($verifyAction->execute($customer->id, 111222));
    }

    public function test_reset_password_action_updates_password_and_clears_otp(): void
    {
        $customer = Customer::create([
            'name' => 'Rifat Hossain',
            'slug' => 'rifat-hossain-01922334455',
            'phone' => '01922334455',
            'password' => Hash::make('initialpass'),
            'status' => 1,
            'forgot' => 778899,
        ]);

        $resetAction = new ResetCustomerPasswordAction();
        $success = $resetAction->execute($customer->id, 778899, 'newbrandpass123');

        $this->assertTrue($success);

        $customer->refresh();
        $this->assertNull($customer->forgot);
        $this->assertTrue(Hash::check('newbrandpass123', $customer->password));

        // Reusing the same OTP should fail
        $this->expectException(InvalidArgumentException::class);
        $resetAction->execute($customer->id, 778899, 'anotherpass');
    }

    public function test_resend_password_reset_otp(): void
    {
        Event::fake([CustomerPasswordResetRequestedEvent::class]);

        $customer = Customer::create([
            'name' => 'Tasnim Rahman',
            'slug' => 'tasnim-rahman-01622334455',
            'phone' => '01622334455',
            'password' => Hash::make('currentpass'),
            'status' => 1,
            'forgot' => 123456,
        ]);

        $resendAction = new ResendPasswordResetOtpAction($this->smsService);
        $newOtp = $resendAction->execute($customer->id);

        $customer->refresh();
        $this->assertEquals($newOtp, (int) $customer->forgot);
        Event::assertDispatched(CustomerPasswordResetRequestedEvent::class);
    }
}