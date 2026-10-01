<?php

namespace Modules\Customer\Application\Actions;

use Illuminate\Support\Facades\Auth;

class LogoutCustomerAction
{
    public function execute(): void
    {
        Auth::guard('customer')->logout();
    }
}