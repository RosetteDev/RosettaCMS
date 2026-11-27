<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Socialite\Facades\Socialite;
use Throwable;
use App\Http\Controllers\Controller;

class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the social provider's consent screen.
     */
    public function redirect(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'github'], true)) {
            abort(404);
        }

        $settings = $this->settings();

        if (!$this->socialProviderEnabled($provider, $settings)) {
            return redirect()->route('login')
                ->with('error', ucfirst($provider) . ' sign-in is not enabled.');
        }

        $this->ensureIsNotRateLimited($request);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from the social provider.
     */
    public function callback(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'github'], true)) {
            abort(404);
        }

        $settings = $this->settings();

        if (!$this->socialProviderEnabled($provider, $settings)) {
            return redirect()->route('login')
                ->with('error', ucfirst($provider) . ' sign-in is not enabled.');
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('login')
                ->with('error', 'Unable to complete ' . ucfirst($provider) . ' sign-in. Please try again.');
        }

        $email = $socialUser->getEmail();

        if (!$email) {
            return redirect()->route('login')
                ->with('error', 'Your ' . ucfirst($provider) . ' account did not provide an email address.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = $this->createUserFromSocial($provider, $socialUser, $email, $settings);
        }

        Auth::login($user, true);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Create a new local user from a social account.
     */
    protected function createUserFromSocial(string $provider, $socialUser, string $email, $settings): User
    {
        $name = $socialUser->getName()
            ?: ($provider === 'github' ? $socialUser->getNickname() : null)
            ?: explode('@', $email)[0];

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(bin2hex(random_bytes(24))),
            'email_verified_at' => now(),
        ]);

        if ($role = Role::where('slug', 'subscriber')->first()) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        return $user;
    }

    /**
     * Check whether a social provider is enabled in settings.
     */
    protected function socialProviderEnabled(string $provider, $settings): bool
    {
        $key = $provider === 'google' ? 'enable_social_login_google' : 'enable_social_login_github';

        return (bool) ($settings->{$key} ?? false);
    }

    protected function settings()
    {
        return SiteSetting::forSite();
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        $key = 'social-login|' . sha1($request->ip());

        if (!RateLimiter::tooManyAttempts($key, 5)) {
            RateLimiter::hit($key, 60);

            return;
        }

        $seconds = RateLimiter::availableIn($key);

        throw \Illuminate\Validation\ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
