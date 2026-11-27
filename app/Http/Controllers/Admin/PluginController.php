<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PluginApproval;
use App\Services\PluginEcosystemService;
use Illuminate\Http\Request;
use RosettaCMS\Plugins\WordPress\WordPressPluginLoader;
use RosettaCMS\PluginManager;

class PluginController extends Controller
{
    /**
     * List built-in Rosetta plugins and discoverable WordPress plugins.
     */
    public function index(PluginManager $manager, WordPressPluginLoader $loader, PluginEcosystemService $ecosystem)
    {
        $builtIn = $manager->scan();
        $activeBuiltIn = $manager->getActivePlugins();
        $wordpress = $loader->discover();

        $ecosystemPlugins = $ecosystem->discover();

        // Map each plugin id to its approved permissions.
        $pluginIds = array_map(fn ($p) => $p['id'], $ecosystemPlugins);
        $approvals = PluginApproval::whereIn('plugin_id', $pluginIds)->get()
            ->groupBy('plugin_id')
            ->map(fn ($rows) => $rows->pluck('permission')->all())
            ->toArray();

        foreach ($ecosystemPlugins as &$plugin) {
            $plugin['approved_permissions'] = $approvals[$plugin['id']] ?? [];
            $plugin['missing_permissions'] = $ecosystem->missingPermissions(
                $plugin['permissions'],
                $plugin['approved_permissions']
            );
        }
        unset($plugin);

        return view('admin.plugins.index', [
            'builtIn' => $builtIn,
            'activeBuiltIn' => $activeBuiltIn,
            'wordpress' => $wordpress,
            'activeWordPress' => $loader->activeIds(),
            'paths' => $loader->paths(),
            'ecosystemPlugins' => $ecosystemPlugins,
            'ecosystemPermissions' => PluginEcosystemService::PERMISSIONS,
        ]);
    }

    /**
     * Approve a permission for a third-party plugin.
     */
    public function approve(Request $request, string $slug, string $permission)
    {
        if (!in_array($permission, PluginEcosystemService::PERMISSIONS, true)) {
            return back()->with('error', 'Unknown permission.');
        }

        PluginApproval::firstOrCreate(
            ['plugin_id' => $slug, 'permission' => $permission],
            ['user_id' => $request->user()?->id, 'approved_at' => now()]
        );

        return back()->with('success', 'Permission approved for ' . $slug . '.');
    }

    /**
     * Revoke a permission for a third-party plugin.
     */
    public function revoke(string $slug, string $permission)
    {
        PluginApproval::where('plugin_id', $slug)
            ->where('permission', $permission)
            ->delete();

        return back()->with('success', 'Permission revoked for ' . $slug . '.');
    }

    /**
     * Activate a WordPress plugin.
     */
    public function activate(string $id, WordPressPluginLoader $loader)
    {
        if (!$loader->activate($id)) {
            return back()->with('error', 'That plugin could not be found.');
        }

        return back()->with('success', 'Plugin activated. It will load on the next request.');
    }

    /**
     * Deactivate a WordPress plugin.
     */
    public function deactivate(string $id, WordPressPluginLoader $loader)
    {
        $loader->deactivate($id);

        return back()->with('success', 'Plugin deactivated.');
    }
}
