<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ThemeController extends Controller
{
    /**
     * Display a listing of available themes.
     */
    public function index(Request $request)
    {
        $themes = $this->getAvailableThemes();
        $currentSite = $request->input('site_id') ? Site::find($request->input('site_id')) : Site::first();
        
        return view('admin.themes.index', compact('themes', 'currentSite'));
    }

    /**
     * Get available themes from the themes directory.
     */
    protected function getAvailableThemes()
    {
        $themes = [];
        $themeDir = base_path('themes');
        
        if (!is_dir($themeDir)) {
            return $themes;
        }
        
        $directories = File::directories($themeDir);
        
        foreach ($directories as $directory) {
            $themeJson = $directory . '/theme.json';
            
            if (File::exists($themeJson)) {
                $content = File::get($themeJson);
                $themeData = json_decode($content, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    $themeData['directory'] = basename($directory);
                    $themeData['path'] = $directory;
                    
                    // Check if screenshot exists
                    $screenshotPath = $directory . '/screenshot.png';
                    $themeData['has_screenshot'] = File::exists($screenshotPath);
                    
                    // Get screenshots URL
                    if ($themeData['has_screenshot']) {
                        $themeData['screenshot_url'] = url('themes/' . basename($directory) . '/screenshot.png');
                    }
                    
                    $themes[] = $themeData;
                }
            }
        }
        
        return $themes;
    }

    /**
     * Show the form for editing a theme.
     */
    public function edit($slug)
    {
        $themes = $this->getAvailableThemes();
        $theme = collect($themes)->firstWhere('slug', $slug);

        if (!$theme) {
            abort(404, 'Theme not found.');
        }

        $currentSite = request('site_id')
            ? Site::find(request('site_id'))
            : Site::first();

        $isActive = $currentSite && $currentSite->theme === $slug;

        $renderable = file_exists(resource_path('views/themes/' . $slug . '/home.blade.php'))
            || file_exists(base_path('themes/' . $slug . '/views/layouts/app.blade.php'));

        return view('admin.themes.edit', compact('theme', 'currentSite', 'isActive', 'renderable'));
    }

    /**
     * Update a theme's active status.
     */
    public function update(Request $request, $slug)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'active' => 'nullable|boolean',
        ]);

        $themes = $this->getAvailableThemes();
        $theme = collect($themes)->firstWhere('slug', $slug);

        if (!$theme) {
            abort(404, 'Theme not found.');
        }

        $site = Site::findOrFail($request->site_id);

        if ($request->boolean('active')) {
            // Only allow activation of themes that have a renderable home view,
            // otherwise the public site would break after switching.
            $renderable = file_exists(resource_path('views/themes/' . $slug . '/home.blade.php'))
                || file_exists(base_path('themes/' . $slug . '/views/layouts/app.blade.php'));

            if (!$renderable) {
                return redirect()->route('admin.themes.index')
                    ->with('error', "Theme '{$slug}' is not ready to activate yet (missing view files).");
            }

            $site->update([
                'theme' => $slug,
            ]);

            return redirect()->route('admin.themes.index')
                ->with('success', "Theme '{$slug}' has been activated.");
        }

        return redirect()->route('admin.themes.index')
            ->with('error', 'No changes made.');
    }

    /**
     * Remove a theme from the system.
     */
    public function destroy($slug)
    {
        // Note: Themes are typically deleted manually from the filesystem
        // This method would be for cleanup purposes
        return redirect()->back()
            ->with('info', 'Themes are managed via the filesystem. Please delete the theme folder manually.');
    }
}
