<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $settings = SiteSetting::firstOrNew([]);
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string',
            'admin_email' => 'required|email',
            'posts_per_page' => 'required|integer|min:1|max:100',
            'theme' => 'nullable|string|max:255',
        ]);

        $settings = SiteSetting::firstOrNew([]);
        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'Settings updated successfully.');
    }
}
