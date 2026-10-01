<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token = null)
    {
        return redirect()->route('customer.forgot_password');
    }

    public function reset(Request $request)
    {
        return redirect()->route('customer.forgot_password');
    }
}
