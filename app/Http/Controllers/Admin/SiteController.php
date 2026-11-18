<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of sites.
     */
    public function index()
    {
        $sites = Site::withCount('posts', 'media')->paginate(20);
        
        $settings = SiteSetting::forSite();
        
        return view('admin.sites.index', compact('sites', 'settings'));
    }

    /**
     * Show the form for creating a new site.
     */
    public function create()
    {
        return view('admin.sites.create');
    }

    /**
     * Store a newly created site.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:sites,domain',
            'description' => 'nullable|string',
        ]);

        Site::create($validated);

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site created successfully.');
    }

    /**
     * Display the specified site.
     */
    public function show(Site $site)
    {
        return view('admin.sites.show', compact('site'));
    }

    /**
     * Show the form for editing the specified site.
     */
    public function edit(Site $site)
    {
        return view('admin.sites.edit', compact('site'));
    }

    /**
     * Update the specified site.
     */
    public function update(Request $request, Site $site)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'nullable|string|max:255|unique:sites,domain,' . $site->id,
            'description' => 'nullable|string',
            'home_post_id' => 'nullable|exists:posts,id',
        ]);

        $site->update($validated);

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site updated successfully.');
    }

    /**
     * Remove the specified site.
     */
    public function destroy(Site $site)
    {
        // Prevent deletion of the last site
        if (Site::count() === 1) {
            return back()->withErrors(['error' => 'Cannot delete the last site.']);
        }

        $site->delete();
        return redirect()->route('admin.sites.index')
            ->with('success', 'Site deleted successfully.');
    }
}
