<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteIpRule;
use App\Services\IpMatchService;
use Illuminate\Http\Request;

class IpRulesController extends Controller
{
    public function __construct(protected IpMatchService $ipMatch)
    {
    }

    /**
     * Show the IP allow/block rules for the current site.
     */
    public function index()
    {
        $site = app('CurrentSite');

        $rules = $site ? $site->ipRules()->latest()->get() : collect();

        return view('admin.ip-rules.index', compact('site', 'rules'));
    }

    /**
     * Add a rule to the current site.
     */
    public function store(Request $request)
    {
        $site = app('CurrentSite');

        if (!$site) {
            return back()->with('error', 'No site is resolved for this request.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:allow,block'],
            'cidr' => ['required', 'string', 'max:64'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->ipMatch->validate($validated['cidr']);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['cidr' => $e->getMessage()])->withInput();
        }

        $site->ipRules()->create([
            'type' => $validated['type'],
            'cidr' => trim($validated['cidr']),
            'label' => $validated['label'] ?? null,
        ]);

        return redirect()->route('admin.ip-rules.index')
            ->with('success', 'IP rule added.');
    }

    /**
     * Delete a rule.
     */
    public function destroy(SiteIpRule $rule)
    {
        $site = app('CurrentSite');

        if (!$site || $rule->site_id !== $site->id) {
            abort(404);
        }

        $rule->delete();

        return redirect()->route('admin.ip-rules.index')
            ->with('success', 'IP rule removed.');
    }
}
