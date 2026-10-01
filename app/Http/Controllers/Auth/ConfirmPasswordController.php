<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class ConfirmPasswordController extends Controller
{
    protected string $redirectTo = '/admin/dashboard';

    public function __construct()
    {
        $this->middleware('auth');
    }
}
