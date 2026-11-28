<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the "forgot password" form.
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given email address.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // Neutral response: always the same status message, whether or not the
        // email is registered. The client must not learn whether an account
        // exists; the real outcome is only visible in the server log.
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If your email is registered, a reset link has been sent.');
    }
}
