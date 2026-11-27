<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorService $twoFactor)
    {
    }

    /**
     * Start the two-factor setup: generate a secret and keep it in the session
     * until the user confirms a valid code.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactor()) {
            return redirect()->route('profile')
                ->withErrors(['code' => 'Two-factor authentication is already enabled.']);
        }

        $secret = $this->twoFactor->generateSecret();

        $request->session()->put('two_factor_setup_secret', $secret);

        return redirect()->route('profile')->with('two_factor_setup', true);
    }

    /**
     * Confirm the secret by verifying a code from the authenticator app.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $secret = $request->session()->pull('two_factor_setup_secret');

        if (!$secret) {
            return redirect()->route('profile')
                ->withErrors(['code' => 'No pending two-factor setup — start one first.']);
        }

        if (!$this->twoFactor->verifyCode($secret, $request->input('code'))) {
            $request->session()->put('two_factor_setup_secret', $secret);

            return redirect()->route('profile')
                ->withErrors(['code' => 'The code you entered is invalid.']);
        }

        $user = $request->user();
        $user->two_factor_secret = $secret;
        $user->two_factor_enabled_at = now();
        $user->save();

        ActivityLog::record('auth.two_factor_enabled', 'Enabled two-factor authentication', 'user', $user->id);

        return redirect()->route('profile')
            ->with('success', 'Two-factor authentication is now enabled.');
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (!$user->hasTwoFactor()) {
            return redirect()->route('profile')
                ->withErrors(['password' => 'Two-factor authentication is not enabled.']);
        }

        $user->two_factor_secret = null;
        $user->two_factor_enabled_at = null;
        $user->save();

        $request->session()->forget('two_factor_setup_secret');

        ActivityLog::record('auth.two_factor_disabled', 'Disabled two-factor authentication', 'user', $user->id);

        return redirect()->route('profile')
            ->with('success', 'Two-factor authentication has been disabled.');
    }
}
