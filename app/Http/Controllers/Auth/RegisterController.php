<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration attempt.
     */
    public function register(Request $request)
    {
        $this->ensureIsNotRateLimited($request);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        RateLimiter::clear($this->registerRateLimiterKey($request));

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    /**
     * Determine if the user is rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request)
    {
        if (!RateLimiter::tooManyAttempts($this->registerRateLimiterKey($request), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->registerRateLimiterKey($request));

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
    protected function registerRateLimiterKey(Request $request)
    {
        return $request->ip() . ':' . $request->email;
    }
}
