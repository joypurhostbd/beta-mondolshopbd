<?php

namespace Modules\Customer\Application\Actions;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\Customer\Application\DTOs\CustomerDTO;
use Modules\Customer\Domain\Contracts\CustomerRepositoryInterface;
use Shared\Domain\Exceptions\EntityNotFoundException;
use Shared\Domain\ValueObjects\PhoneNumber;

class AuthenticateCustomerAction
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository
    ) {}

    public function execute(string $phoneOrEmail, string $password): ?CustomerDTO
    {
        $customer = null;
        try {
            $phone = PhoneNumber::from($phoneOrEmail)->getValue();
            $customer = Customer::where('phone', $phone)->first();
        } catch (\Throwable) {
            $customer = Customer::where('email', strtolower(trim($phoneOrEmail)))->first();
        }

        if (!$customer) {
            return null;
        }

        if (!Hash::check($password, $customer->password)) {
            return null;
        }

        if ((int) $customer->status !== 1) {
            return null;
        }

        Auth::guard('customer')->login($customer);

        $entity = $this->customerRepository->findById($customer->id);
        return CustomerDTO::fromEntity($entity);
    }
}