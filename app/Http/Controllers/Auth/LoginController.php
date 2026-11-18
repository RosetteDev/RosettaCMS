<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request)
    {
        $this->ensureIsNotRateLimited($request);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($this->loginRateLimiterKey($request));

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::increment($this->loginRateLimiterKey($request));

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Determine if the user is rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request)
    {
        if (!RateLimiter::tooManyAttempts($this->loginRateLimiterKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->loginRateLimiterKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiter key.
     */
    protected function loginRateLimiterKey(Request $request)
    {
        return $request->ip() . ':' . $request->email;
    }

    /**
     * Log out the user.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
