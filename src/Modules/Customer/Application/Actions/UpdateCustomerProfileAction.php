<?php

namespace Modules\Customer\Application\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Application\DTOs\CustomerDTO;
use Modules\Customer\Domain\Contracts\CustomerRepositoryInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;

class UpdateCustomerProfileAction
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(int|string $customerId, array $data): CustomerDTO
    {
        return DB::transaction(function () use ($customerId, $data) {
            $customer = Customer::find($customerId);
            if (!$customer) {
                throw EntityNotFoundException::forEntity('Customer', $customerId);
            }

            $customer->update([
                'name' => $data['name'] ?? $customer->name,
                'email' => $data['email'] ?? $customer->email,
                'address' => $data['address'] ?? $customer->address,
                'district' => $data['district'] ?? $customer->district,
                'area' => $data['area'] ?? $customer->area,
                'image' => $data['image'] ?? $customer->image,
            ]);

            $entity = $this->customerRepository->findById($customer->id);
            return CustomerDTO::fromEntity($entity);
        });
    }
}