<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use App\Models\User;
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
        $registrationEnabled = false;

        try {
            $registrationEnabled = (bool) (SiteSetting::forSite()->enable_user_registration ?? true);
        } catch (\Throwable $e) {
            // Fall back to hiding the link if settings are unavailable.
        }

        return view('auth.login', compact('registrationEnabled'));
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

        // Brute-force lockout: reject the attempt while the account is locked,
        // even if the password would be correct.
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->isLocked()) {
            ActivityLog::record('auth.locked', 'Login blocked: account locked until ' . $user->locked_until->toDayDateTimeString(), 'user', $user->id, $user->id);

            throw ValidationException::withMessages([
                'email' => "Account temporarily locked. Try again after " . $user->locked_until->format('H:i') . '.',
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = $request->user();

            // Two-factor challenge: do not hand out a session until the code checks out.
            if ($user && $user->hasTwoFactor()) {
                $userId = $user->id;

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->put('two_factor_user_id', $userId);

                return redirect()->route('login.2fa');
            }

            $user?->resetFailedLogins();
            $request->session()->regenerate();
            RateLimiter::clear($this->loginRateLimiterKey($request));

            ActivityLog::record('auth.login', 'Signed in', 'user', $user?->id, $user?->id);

            return redirect()->intended(route('dashboard'));
        }

        if ($user) {
            $user->registerFailedLogin();

            ActivityLog::record('auth.failed_login', 'Failed login attempt', 'user', $user->id, $user->id);

            if ($user->isLocked()) {
                ActivityLog::record('auth.locked', 'Account locked for 15 minutes after repeated failures', 'user', $user->id, $user->id);
            }
        }

        RateLimiter::increment($this->loginRateLimiterKey($request));

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    /**
     * Show the two-factor challenge form.
     */
    public function showTwoFactorForm(Request $request)
    {
        if (!$request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    /**
     * Verify a two-factor code.
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->pull('two_factor_user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$user || !$user->hasTwoFactor()) {
            return redirect()->route('login');
        }

        if (!app(\App\Services\TwoFactorService::class)->verifyCode($user->two_factor_secret, $request->input('code'))) {
            $request->session()->put('two_factor_user_id', $user->id);

            ActivityLog::record('auth.two_factor_failed', 'Failed two-factor attempt', 'user', $user->id, $user->id);

            return back()->withErrors(['code' => 'The code you entered is invalid.']);
        }

        $request->session()->regenerate();
        Auth::login($user, true);

        ActivityLog::record('auth.login', 'Signed in with two-factor authentication', 'user', $user->id, $user->id);

        return redirect()->intended(route('dashboard'));
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
        // Key by IP only so the limit cannot be bypassed by rotating the email field.
        return $request->ip();
    }

    /**
     * Log out the user.
     */
    public function logout(Request $request)
    {
        ActivityLog::record('auth.logout', 'Signed out', 'user', $request->user()?->id, $request->user()?->id);

        $request->session()->forget('two_factor_user_id');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
