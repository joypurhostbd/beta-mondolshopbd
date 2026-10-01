<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected string $redirectTo = '/admin/dashboard';

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm()
    {
        return redirect()->route('customer.register');
    }

    /**
     * Handle registration request.
     */
    public function register()
    {
        return redirect()->route('customer.register');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
}
