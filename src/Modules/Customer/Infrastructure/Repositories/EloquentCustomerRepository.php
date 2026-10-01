<?php

namespace Modules\Customer\Infrastructure\Repositories;

use App\Models\Customer;
use Modules\Customer\Domain\Contracts\CustomerRepositoryInterface;
use Modules\Customer\Domain\Entities\CustomerEntity;
use Shared\Domain\Contracts\EntityInterface;
use Shared\Domain\Enums\CustomerStatusEnum;
use Shared\Domain\ValueObjects\Email;
use Shared\Domain\ValueObjects\Money;
use Shared\Domain\ValueObjects\PhoneNumber;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function findById(int|string $id): ?CustomerEntity
    {
        $customer = Customer::find($id);
        return $customer ? $this->toEntity($customer) : null;
    }

    public function findByPhone(string $phone): ?CustomerEntity
    {
        try {
            $normalized = PhoneNumber::from($phone)->getValue();
            $customer = Customer::where('phone', $normalized)
                ->orWhere('phone', $phone)
                ->first();
        } catch (\Throwable) {
            $customer = Customer::where('phone', $phone)->first();
        }

        return $customer ? $this->toEntity($customer) : null;
    }

    public function findByEmail(string $email): ?CustomerEntity
    {
        $customer = Customer::where('email', strtolower(trim($email)))->first();
        return $customer ? $this->toEntity($customer) : null;
    }

    public function save(EntityInterface $entity): EntityInterface
    {
        /** @var CustomerEntity $customerEntity */
        $customerEntity = $entity;

        $phoneVal = $customerEntity->getPhone()->getValue();
        $slug = \Illuminate\Support\Str::slug($customerEntity->getName() . '-' . $phoneVal);

        $customer = Customer::updateOrCreate(
            ['id' => $customerEntity->getId()],
            [
                'name' => $customerEntity->getName(),
                'slug' => $slug,
                'phone' => $phoneVal,
                'email' => $customerEntity->getEmail()?->getValue(),
                'address' => $customerEntity->getAddress(),
                'district' => $customerEntity->getDistrict(),
                'area' => $customerEntity->getArea(),
                'status' => $customerEntity->getStatus()->value === 'active' ? 1 : 0,
                'balance' => $customerEntity->getBalance()->getAmount(),
                'verify' => $customerEntity->isVerified() ? 1 : 0,
            ]
        );

        return $this->toEntity($customer);
    }

    public function deleteById(int|string $id): bool
    {
        return (bool) Customer::destroy($id);
    }

    private function toEntity(Customer $customer): CustomerEntity
    {
        return new CustomerEntity(
            id: $customer->id,
            name: $customer->name ?? '',
            phone: PhoneNumber::from($customer->phone ?? '01700000000'),
            email: $customer->email ? Email::from($customer->email) : null,
            address: $customer->address,
            district: (string) ($customer->district ?? ''),
            area: (string) ($customer->area ?? ''),
            status: CustomerStatusEnum::tryFrom((int) ($customer->status ?? 1)) ?? CustomerStatusEnum::ACTIVE,
            balance: Money::from($customer->balance ?? 0),
            verify: (int) ($customer->verify ?? 1)
        );
    }
}