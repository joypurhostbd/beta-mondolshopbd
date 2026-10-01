<?php

namespace Tests\Unit;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Customer\Application\DTOs\CustomerDTO;
use Modules\Customer\Domain\Entities\CustomerEntity;
use Modules\Customer\Domain\Events\CustomerPasswordResetRequestedEvent;
use Modules\Customer\Domain\Events\CustomerRegisteredEvent;
use Shared\Domain\Contracts\Modules\CustomerModuleInterface;
use Shared\Domain\Enums\CustomerStatusEnum;
use Shared\Domain\ValueObjects\Email;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;
use Tests\TestCase;

class CustomerModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_entity_and_dto_encapsulation(): void
    {
        $entity = new CustomerEntity(
            id: 10,
            name: 'Tanvir Ahmed',
            phone: PhoneNumber::from('01711122233'),
            email: Email::from('tanvir@example.com'),
            address: 'Dhanmondi 32, Dhaka',
            district: 'Dhaka',
            area: 'Dhanmondi',
            status: CustomerStatusEnum::ACTIVE,
            balance: Money::from(500),
            verify: 1
        );

        $this->assertEquals('Tanvir Ahmed', $entity->getName());
        $this->assertEquals('01711122233', $entity->getPhone()->getValue());
        $this->assertEquals('tanvir@example.com', $entity->getEmail()?->getValue());
        $this->assertTrue($entity->isActive());
        $this->assertTrue($entity->isVerified());

        $dto = CustomerDTO::fromEntity($entity);
        $this->assertEquals(10, $dto->id);
        $this->assertEquals('01711122233', $dto->phone);
        $this->assertEquals(500.0, $dto->balance);
    }

    public function test_customer_events_payload(): void
    {
        $regEvent = new CustomerRegisteredEvent(15, 'Samiul Islam', '01811223344');
        $this->assertEquals('customer.registered', $regEvent->getEventName());
        $this->assertEquals(['customer_id' => 15, 'name' => 'Samiul Islam', 'phone' => '01811223344'], $regEvent->toPayload());

        $resetEvent = new CustomerPasswordResetRequestedEvent(15, '01811223344', 458921);
        $this->assertEquals('customer.password_reset_requested', $resetEvent->getEventName());
        $this->assertEquals(['customer_id' => 15, 'phone' => '01811223344', 'otp' => 458921], $resetEvent->toPayload());
    }

    public function test_customer_service_implements_module_contract(): void
    {
        $cust = Customer::create([
            'name' => 'Farhan Kabir',
            'slug' => 'farhan-kabir-01911223344',
            'phone' => '01911223344',
            'email' => 'farhan@example.com',
            'password' => bcrypt('secret123'),
            'status' => 1,
            'verify' => 1,
            'balance' => 250,
        ]);

        $service = $this->app->make(CustomerModuleInterface::class);

        $foundById = $service->findCustomerById($cust->id);
        $this->assertNotNull($foundById);
        $this->assertEquals('Farhan Kabir', $foundById['name']);
        $this->assertEquals('01911223344', $foundById['phone']);

        $foundByPhone = $service->findCustomerByPhone('01911223344');
        $this->assertNotNull($foundByPhone);
        $this->assertEquals('Farhan Kabir', $foundByPhone['name']);

        $notFound = $service->findCustomerByPhone('01300000000');
        $this->assertNull($notFound);
    }
}