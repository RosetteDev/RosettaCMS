<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /**
     * Show the redirect map for the current site.
     */
    public function index()
    {
        $site = app('CurrentSite');

        $redirects = $site
            ? $site->redirects()->latest()->get()
            : collect();

        return view('admin.redirects.index', compact('site', 'redirects'));
    }

    /**
     * Add a redirect to the current site.
     */
    public function store(Request $request)
    {
        $site = app('CurrentSite');

        if (!$site) {
            return back()->with('error', 'No site is resolved for this request.');
        }

        $validated = $request->validate([
            'from_path' => ['required', 'string', 'max:255'],
            'to_url' => ['required', 'string', 'max:2048'],
            'type' => ['required', 'in:301,302'],
        ]);

        $from = Redirect::normalize($validated['from_path']);

        if ($from === '') {
            return back()->withErrors(['from_path' => 'A from path is required.'])->withInput();
        }

        if (preg_match('#^https?://#i', $validated['to_url'])) {
            // Absolute URL — allowed as-is.
        } elseif (!preg_match('#^/#', $validated['to_url'])) {
            $validated['to_url'] = '/' . ltrim($validated['to_url'], '/');
        }

        $existing = $site->redirects()->where('from_path', $from)->first();

        if ($existing) {
            return back()->withErrors(['from_path' => 'A redirect already exists for this path.'])->withInput();
        }

        $site->redirects()->create([
            'from_path' => $from,
            'to_url' => $validated['to_url'],
            'type' => $validated['type'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.redirects.index')
            ->with('success', 'Redirect added.');
    }

    /**
     * Toggle the active state of a redirect.
     */
    public function toggle(Redirect $redirect)
    {
        $site = app('CurrentSite');

        if (!$site || $redirect->site_id !== $site->id) {
            abort(404);
        }

        $redirect->update(['is_active' => !$redirect->is_active]);

        return redirect()->route('admin.redirects.index')
            ->with('success', $redirect->is_active ? 'Redirect enabled.' : 'Redirect disabled.');
    }

    /**
     * Delete a redirect.
     */
    public function destroy(Redirect $redirect)
    {
        $site = app('CurrentSite');

        if (!$site || $redirect->site_id !== $site->id) {
            abort(404);
        }

        $redirect->delete();

        return redirect()->route('admin.redirects.index')
            ->with('success', 'Redirect removed.');
    }
}
