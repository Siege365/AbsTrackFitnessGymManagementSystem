<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Rules\Turnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        if ($this->turnstileEnabled()) {
            $request->validate([
                'cf-turnstile-response' => ['required', new Turnstile],
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            ActivityLog::create([
                'user_id'     => Auth::id(),
                'user_name'   => Auth::user()->name,
                'action'      => 'login',
                'module'      => 'auth',
                'description' => Auth::user()->name . ' logged in',
                'ip_address'  => $request->ip(),
            ]);

            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    private function turnstileEnabled(): bool
    {
        return filled(Config::get('services.turnstile.site_key'))
            && filled(Config::get('services.turnstile.secret_key'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            ActivityLog::create([
                'user_id'     => $user->id,
                'user_name'   => $user->name,
                'action'      => 'logout',
                'module'      => 'auth',
                'description' => $user->name . ' logged out',
                'ip_address'  => $request->ip(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
