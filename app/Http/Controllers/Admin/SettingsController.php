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
            // Spam protection settings
            'akismet_api_key' => 'nullable|string',
            'enable_akismet' => 'nullable|boolean',
            'spam_keywords' => 'nullable|string',
            'rate_limit_enabled' => 'nullable|boolean',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:100',
            'captcha_enabled' => 'nullable|boolean',
            'enable_comment_approval' => 'nullable|boolean',
        ]);

        $settings = SiteSetting::firstOrNew([]);
        $settings->fill($validated);
        $settings->save();

        return back()->with('success', 'Settings updated successfully.');
    }
}
