<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return redirect()->route('customer.forgot_password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        return redirect()->route('customer.forgot_password');
    }
}
