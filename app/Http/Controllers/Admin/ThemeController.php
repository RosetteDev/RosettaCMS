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
                    
                    // Generate screenshots if they don't exist
                    $this->ensureScreenshots($directory);
                    
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
     * Ensure screenshot files exist for a theme directory.
     * Converts SVG screenshots to PNG if needed.
     */
    protected function ensureScreenshots(string $themePath)
    {
        $svgScreenshot = $themePath . '/screenshot.svg';
        $pngScreenshot = $themePath . '/screenshot.png';
        
        // If PNG exists and is newer than SVG, skip
        if (File::exists($pngScreenshot) && File::exists($svgScreenshot)) {
            if (File::lastModified($pngScreenshot) >= File::lastModified($svgScreenshot)) {
                return;
            }
        }
        
        // If SVG exists but no PNG, try to convert
        if (File::exists($svgScreenshot) && !File::exists($pngScreenshot)) {
            $this->convertSvgToPng($svgScreenshot, $pngScreenshot);
        }
    }

    /**
     * Convert SVG screenshot to PNG using GD library.
     */
    protected function convertSvgToPng(string $svgPath, string $pngPath)
    {
        $svgContent = File::get($svgPath);
        
        // Extract dimensions from SVG
        $width = 1200;
        $height = 800;
        if (preg_match('/width=["\'](\d+)["\']/', $svgContent, $m)) {
            $width = (int) $m[1];
        }
        if (preg_match('/height=["\'](\d+)["\']/', $svgContent, $m)) {
            $height = (int) $m[1];
        }
        
        // Create image
        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        
        // Fill with transparent background
        $transparency = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparency);
        
        // Simple SVG rendering (basic shapes only)
        // This is a lightweight implementation - for production, consider Imagick
        if (class_exists('Imagick')) {
            $imagick = new Imagick();
            $imagick->readImageBlob($svgContent);
            $imagick->setImageFormat('png32');
            $imagick->writeImage($pngPath);
            $imagick->clear();
            return;
        }
        
        // Fallback: use GD to draw basic shapes
        // Parse SVG and draw basic shapes
        $this->renderBasicSvgToGd($svgContent, $img, $width, $height);
        
        // Save PNG
        imagepng($img, $pngPath);
        imagedestroy($img);
    }

    /**
     * Basic SVG rendering to GD image (for simple screenshots).
     */
    protected function renderBasicSvgToGd(string $svgContent, $gdImage, int $width, int $height)
    {
        // Parse SVG and draw basic shapes using GD
        // This is a simplified parser for common SVG elements
        
        $pattern = '/<rect[^>]*x=["\'](\d+)["\'][^>]*y=["\'](\d+)["\'][^>]*width=["\'](\d+)["\'][^>]*height=["\'](\d+)["\'][^>]*fill=["\']#([0-9a-fA-F]{6})["\'][^>]*>/i';
        preg_match_all($pattern, $svgContent, $rects, PREG_SET_ORDER);
        
        foreach ($rects as $rect) {
            $x = (int) $rect[1];
            $y = (int) $rect[2];
            $w = (int) $rect[3];
            $h = (int) $rect[4];
            $color = $this->hexToRgb($rect[5]);
            
            $colorResource = imagecolorallocate(
                $gdImage,
                $color['r'],
                $color['g'],
                $color['b']
            );
            
            // Fill background first
            imagefilledrectangle($gdImage, $x, $y, $x + $w, $y + $h, $colorResource);
        }
        
        // Draw text
        $pattern = '/<text[^>]*x=["\'](\d+)["\'][^>]*y=["\'](\d+)["\'][^>]*fill=["\']#([0-9a-fA-F]{6})["\'][^>]*>([^<]+)<\/text>/i';
        preg_match_all($pattern, $svgContent, $texts, PREG_SET_ORDER);
        
        foreach ($texts as $text) {
            $x = (int) $text[1];
            $y = (int) $text[2];
            $color = $this->hexToRgb($text[3]);
            $content = $text[4];
            
            $colorResource = imagecolorallocate(
                $gdImage,
                $color['r'],
                $color['g'],
                $color['b']
            );
            
            // Simple font size mapping
            $fontSize = 12;
            if (preg_match('/font-size=["\'](\d+)["\']/', $text[0], $fs)) {
                $fontSize = (int) $fs[1];
            }
            
            // Simple font mapping
            $fontFile = public_path('fonts/arial.ttf');
            if (!file_exists($fontFile)) {
                // Use built-in font as fallback
                imagettftext($gdImage, $fontSize, 0, $x, $y, $colorResource, $fontFile, $content);
            }
        }
    }

    /**
     * Convert hex color to RGB.
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = str_replace('#', '', $hex);
        $length = strlen($hex);
        
        if ($length == 3) {
            $r = hexdec($hex[0] . $hex[0]);
            $g = hexdec($hex[1] . $hex[1]);
            $b = hexdec($hex[2] . $hex[2]);
        } else {
            $r = hexdec($hex[0] . $hex[1]);
            $g = hexdec($hex[2] . $hex[3]);
            $b = hexdec($hex[4] . $hex[5]);
        }
        
        return ['r' => $r, 'g' => $g, 'b' => $b];
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
     * Install demo content for a theme.
     */
    public function installDemoContent(Request $request, $slug)
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
        ]);

        $result = \App\Services\DemoContentService::install($slug, $request->site_id);

        if ($result['error']) {
            return redirect()->route('admin.themes.index', ['site_id' => $request->site_id])
                ->with('error', $result['error']);
        }

        return redirect()->route('admin.themes.index', ['site_id' => $request->site_id])
            ->with('success', "Installed {$result['created']} demo post(s) for '{$slug}'." . ($result['skipped'] ? " ({$result['skipped']} skipped)" : ''));
    }

    /**
     * Upload and install a theme from a ZIP archive.
     *
     * The archive must contain a theme folder with a theme.json manifest.
     * Uploaded themes are validated (manifest schema, slug format, path
     * traversal) and extracted into the themes/ directory.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'theme_zip' => 'required|file|mimes:zip|max:20480',
        ]);

        $zip = $request->file('theme_zip');
        $tmp = sys_get_temp_dir() . '/theme-upload-' . uniqid() . '.zip';
        $zip->move(sys_get_temp_dir(), basename($tmp));

        try {
            $archive = new \ZipArchive();
            if ($archive->open($tmp) !== true) {
                return redirect()->route('admin.themes.index')->with('error', 'The uploaded file is not a valid ZIP archive.');
            }

            // Scan for theme.json and reject path traversal.
            $themeJsonEntry = null;
            $topLevelDirs = [];

            for ($i = 0; $i < $archive->numFiles; $i++) {
                $name = $archive->getNameIndex($i);

                if ($name === false) {
                    continue;
                }

                $normalized = str_replace('\\', '/', $name);

                if (str_contains($normalized, '..') || str_starts_with($normalized, '/') || preg_match('/^[a-zA-Z]:/', $normalized)) {
                    return redirect()->route('admin.themes.index')->with('error', 'The archive contains unsafe paths and was rejected.');
                }

                if ($normalized === 'theme.json') {
                    $themeJsonEntry = $normalized;
                } elseif (str_ends_with($normalized, '/theme.json')) {
                    $themeJsonEntry = $normalized;
                    $topLevelDirs[] = explode('/', $normalized)[0];
                }
            }

            if ($themeJsonEntry === null) {
                return redirect()->route('admin.themes.index')->with('error', 'The archive does not contain a theme.json manifest.');
            }

            $contents = $archive->getFromName($themeJsonEntry);
            $manifest = json_decode($contents, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($manifest)) {
                return redirect()->route('admin.themes.index')->with('error', 'theme.json is not valid JSON.');
            }

            $slug = $manifest['slug'] ?? null;

            if (empty($slug) || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug)) {
                return redirect()->route('admin.themes.index')->with('error', 'theme.json must declare a valid "slug" (lowercase kebab-case).');
            }

            if (empty($manifest['name'])) {
                return redirect()->route('admin.themes.index')->with('error', 'theme.json must declare a "name".');
            }

            $destination = base_path('themes/' . $slug);

            if (is_dir($destination)) {
                return redirect()->route('admin.themes.index')->with('error', 'A theme named "' . $slug . '" already exists.');
            }

            // Extract into a temp directory, then move into themes/.
            $extractDir = sys_get_temp_dir() . '/theme-extract-' . uniqid();
            mkdir($extractDir, 0755, true);
            $archive->extractTo($extractDir);
            $archive->close();

            // Find the extracted theme folder.
            $extracted = $extractDir;
            $candidate = $extractDir . '/' . $slug;
            if (is_dir($candidate)) {
                $extracted = $candidate;
            } elseif (count(File::directories($extractDir)) === 1) {
                $extracted = File::directories($extractDir)[0];
            } elseif (File::exists($extractDir . '/theme.json')) {
                $extracted = $extractDir;
            } else {
                return redirect()->route('admin.themes.index')->with('error', 'Could not find a theme folder inside the archive.');
            }

            if (!File::exists($extracted . '/theme.json')) {
                return redirect()->route('admin.themes.index')->with('error', 'The extracted folder is missing theme.json.');
            }

            File::move($extracted, $destination);

            // Clean up temp extraction.
            File::deleteDirectory($extractDir);

            return redirect()->route('admin.themes.index')->with('success', 'Theme "' . $slug . '" installed. Activate it from the list or preview it at /?theme=' . $slug . '.');
        } finally {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        }
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
