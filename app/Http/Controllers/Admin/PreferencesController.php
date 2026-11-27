<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Per-user admin preferences: dark mode and dashboard layout.
 */
class PreferencesController extends Controller
{
    /**
     * Allowed dashboard sections (order is the only user-customizable aspect).
     */
    public const SECTIONS = ['recent-content', 'quick-actions', 'recent-media'];

    /**
     * Persist the dark-mode choice on the signed-in user.
     */
    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:light,dark',
        ]);

        $request->user()->update(['admin_theme' => $validated['theme']]);

        return response()->json(['theme' => $validated['theme']]);
    }

    /**
     * Persist the dashboard section order.
     */
    public function updateLayout(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|in:' . implode(',', self::SECTIONS),
        ]);

        // Reject duplicates — a section can only appear once.
        if (count(array_unique($validated['order'])) !== count($validated['order'])) {
            return response()->json(['message' => 'Layout contains duplicate sections.'], 422);
        }

        $request->user()->update(['dashboard_layout' => $validated['order']]);

        return response()->json(['order' => $validated['order']]);
    }
}
