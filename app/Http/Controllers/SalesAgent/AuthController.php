<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /** Wrong passwords allowed per email + device address before a short wait. */
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 60;

    public function showLoginForm()
    {
        if (Auth::guard('sales_agent')->check()) {
            return redirect()->route('agent.dashboard');
        }
        return view('sales_agent.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = 'agent-login|' . Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many wrong attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        $guard = Auth::guard('sales_agent');
        if ($guard->validate($credentials)) {
            // only active agents (inactive and deleted agents cannot log in)
            if ((int) ($guard->getLastAttempted()->status ?? 0) !== 1) {
                return back()->withErrors([
                    'email' => 'Your account is not active. Please contact the office.',
                ])->onlyInput('email');
            }
            $guard->login($guard->getLastAttempted(), $request->boolean('remember'));
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return redirect()->intended(route('agent.dashboard'));
        }

        RateLimiter::hit($throttleKey, self::LOCK_SECONDS);
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('sales_agent')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('agent.login');
    }
}
