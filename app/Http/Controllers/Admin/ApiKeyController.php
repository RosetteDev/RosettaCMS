<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the current user's API keys.
     */
    public function index()
    {
        $keys = Auth::user()->apiKeys()
            ->orderByDesc('created_at')
            ->get();

        return view('admin.api-keys.index', compact('keys'));
    }

    /**
     * Show the form for creating a new API key.
     */
    public function create()
    {
        return view('admin.api-keys.create', [
            'availableScopes' => ApiKey::AVAILABLE_SCOPES,
        ]);
    }

    /**
     * Store a newly created API key.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'scopes' => 'nullable|array',
            'scopes.*' => ['string', Rule::in(ApiKey::AVAILABLE_SCOPES)],
            'expires_at' => 'nullable|date|after:today',
        ]);

        $scopes = array_values(array_unique($validated['scopes'] ?? []));

        $expiresAt = $validated['expires_at'] ?? null;

        $result = ApiKey::createKey(
            Auth::id(),
            $validated['name'],
            $scopes,
            $expiresAt ? \Carbon\Carbon::parse($expiresAt) : null
        );

        // Stash the plaintext key in the session for one-time display on the show page.
        session()->flash('api_key_plaintext', $result['plaintext']);

        return redirect()->route('admin.api-keys.show', $result['key'])
            ->with('success', 'API key created. Copy it now — it will not be shown again.');
    }

    /**
     * Show the newly created key (plaintext shown once).
     */
    public function show(ApiKey $apiKey)
    {
        abort_unless($apiKey->user_id === Auth::id(), 404);

        return view('admin.api-keys.show', [
            'apiKey' => $apiKey,
            // Plaintext is never stored — it is only available from the create flow.
            'plaintext' => session('api_key_plaintext'),
        ]);
    }

    /**
     * Revoke an API key.
     */
    public function destroy(ApiKey $apiKey)
    {
        abort_unless($apiKey->user_id === Auth::id(), 404);

        $apiKey->revoke();

        return redirect()->route('admin.api-keys.index')
            ->with('success', 'API key revoked.');
    }
}
