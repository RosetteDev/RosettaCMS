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
            'site_title' => 'nullable|string|max:255',
            'site_description' => 'nullable|string',
            'post_per_page' => 'nullable|integer|min:1|max:100',
            'theme' => 'nullable|string|max:255',
            'analytics_code' => 'nullable|text',
            'custom_css' => 'nullable|text',
            'custom_js' => 'nullable|text',
            'maintenance_mode' => 'nullable|boolean',
            'disable_comments' => 'nullable|boolean',
        ]);

        $settings = SiteSetting::firstOrNew([]);
        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'Settings updated successfully.');
    }
}
