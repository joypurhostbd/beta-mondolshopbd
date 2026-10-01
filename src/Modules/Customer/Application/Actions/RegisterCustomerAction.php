<?php

namespace Modules\Customer\Application\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Modules\Customer\Application\DTOs\CustomerDTO;
use Modules\Customer\Domain\Contracts\CustomerRepositoryInterface;
use Modules\Customer\Domain\Events\CustomerRegisteredEvent;
use Shared\Domain\ValueObjects\PhoneNumber;

class RegisterCustomerAction
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(array $data): CustomerDTO
    {
        return DB::transaction(function () use ($data) {
            $phoneVO = PhoneNumber::from($data['phone']);
            $phone = $phoneVO->getValue();

            $existing = Customer::where('phone', $phone)->first();
            if ($existing) {
                throw new InvalidArgumentException("Customer already exists with phone: {$phone}");
            }

            $slug = Str::slug(($data['name'] ?? 'customer') . '-' . $phone);

            $customer = Customer::create([
                'name' => $data['name'],
                'slug' => $slug,
                'phone' => $phone,
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'verify' => $data['verify'] ?? 1,
                'status' => $data['status'] ?? 1,
                'balance' => $data['balance'] ?? 0,
            ]);

            event(new CustomerRegisteredEvent($customer->id, $customer->name, $customer->phone));

            $entity = $this->customerRepository->findById($customer->id);
            return CustomerDTO::fromEntity($entity);
        });
    }
}