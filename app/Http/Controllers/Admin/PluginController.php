<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use RosettaCMS\Plugins\WordPress\WordPressPluginLoader;
use RosettaCMS\PluginManager;

class PluginController extends Controller
{
    /**
     * List built-in Rosetta plugins and discoverable WordPress plugins.
     */
    public function index(PluginManager $manager, WordPressPluginLoader $loader)
    {
        $builtIn = $manager->scan();
        $activeBuiltIn = $manager->getActivePlugins();
        $wordpress = $loader->discover();

        return view('admin.plugins.index', [
            'builtIn' => $builtIn,
            'activeBuiltIn' => $activeBuiltIn,
            'wordpress' => $wordpress,
            'activeWordPress' => $loader->activeIds(),
            'paths' => $loader->paths(),
        ]);
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
