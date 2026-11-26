<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        abort_unless($this->registrationEnabled(), 404);

        return view('auth.register');
    }

    /**
     * Handle registration attempt.
     */
    public function register(Request $request)
    {
        abort_unless($this->registrationEnabled(), 404);

        $this->ensureIsNotRateLimited($request);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($role = Role::where('slug', 'subscriber')->first()) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        RateLimiter::clear($this->registerRateLimiterKey($request));

        Auth::login($user);

        return redirect(route('dashboard'));
    }

    /**
     * Check whether public registration is enabled for the site.
     */
    protected function registrationEnabled(): bool
    {
        try {
            return (bool) (SiteSetting::forSite()->enable_user_registration ?? true);
        } catch (Throwable $e) {
            return true;
        }
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
        // Key by IP only so the limit cannot be bypassed by rotating the email field.
        return $request->ip();
    }
}
