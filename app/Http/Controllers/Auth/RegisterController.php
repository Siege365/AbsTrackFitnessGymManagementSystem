<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Rules\Turnstile;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'string', 'min:8'],
            'contact'    => ['nullable', 'string', 'max:255'],
            'address'    => ['nullable', 'string', 'max:500'],
            'cf-turnstile-response' => ['required', new Turnstile],
        ]);

        $user = User::create([
            'name'     => trim($request->first_name . ' ' . $request->last_name),
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'contact'  => $request->contact,
            'address'  => $request->address,
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        // Log the activity under the currently logged-in admin
        ActivityLog::log('created', 'staff', "Registered new staff account: {$user->name}", null, $user->name, $user);

        // Do NOT auto-login the new user — preserve current admin session
        return redirect()->route('staff.index')->with('success', "Staff account for {$user->name} created successfully!");
    }
}
