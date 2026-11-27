<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\SiteSetting;
use App\Services\BrokenLinkChecker;
use Illuminate\Http\Request;

class SeoHealthController extends Controller
{
    public function __construct(protected BrokenLinkChecker $linkChecker)
    {
    }

    /**
     * Show the SEO health report for the current site.
     */
    public function index()
    {
        $site = app('CurrentSite');

        if (!$site) {
            return view('admin.seo-health.index', [
                'site' => null,
                'checks' => [],
                'brokenLinks' => ['broken' => [], 'found' => 0, 'checked' => 0],
                'missingAltCount' => 0,
                'allOk' => false,
            ]);
        }

        $settings = SiteSetting::forSite($site);

        $brokenLinks = $this->linkChecker->check($site->id);
        $missingAltCount = Media::where('site_id', $site->id)
            ->where('mime_type', 'like', 'image/%')
            ->where(function ($query) {
                $query->whereNull('alt_text')->orWhere('alt_text', '');
            })
            ->count();

        $checks = [
            'sitemap' => [
                'label' => 'XML sitemap enabled',
                'ok' => (bool) ($settings->enable_xml_sitemap ?? true),
                'detail' => ($settings->enable_xml_sitemap ?? true)
                    ? 'Served at /sitemap.xml'
                    : 'Sitemap disabled — visitors and search engines cannot discover the site',
                'fix' => 'enable-sitemap',
            ],
            'favicon' => [
                'label' => 'Favicon configured',
                'ok' => !empty($settings->favicon_url),
                'detail' => $settings->favicon_url ?: 'No favicon set — browser tabs show the default icon',
                'fix' => !empty($settings->logo_url) ? 'favicon-from-logo' : null,
            ],
            'og_image' => [
                'label' => 'Default OG image',
                'ok' => !empty($settings->og_image) || !empty($settings->logo_url),
                'detail' => $settings->og_image ?: ($settings->logo_url ? 'Using site logo as the default OG image' : 'No default OG image — social shares have no preview image'),
                'fix' => !empty($settings->logo_url) && empty($settings->og_image) ? 'og-from-logo' : null,
            ],
            'description' => [
                'label' => 'Default meta description',
                'ok' => !empty(trim((string) ($settings->site_description ?? ''))),
                'detail' => !empty(trim((string) ($settings->site_description ?? '')))
                    ? 'Set (used as the site-wide fallback)'
                    : 'No default meta description — pages without one have none',
                'fix' => null,
            ],
            'twitter' => [
                'label' => 'Twitter handle',
                'ok' => !empty(trim((string) ($settings->twitter_handle ?? ''))),
                'detail' => $settings->twitter_handle ?: 'No Twitter handle — twitter:site is not emitted',
                'fix' => null,
            ],
            'verification' => [
                'label' => 'Search Console verification',
                'ok' => !empty(trim((string) ($settings->verification_meta['google'] ?? ''))),
                'detail' => !empty(trim((string) ($settings->verification_meta['google'] ?? '')))
                    ? 'Google site-verification meta present'
                    : 'No verification meta — Search Console / Bing cannot verify the site',
                'fix' => null,
            ],
            'alt_text' => [
                'label' => 'Alt text on all images',
                'ok' => $missingAltCount === 0,
                'detail' => $missingAltCount === 0
                    ? 'Every image in the library has alt text'
                    : "{$missingAltCount} image(s) missing alt text",
                'fix' => null,
            ],
            'broken_links' => [
                'label' => 'Internal links',
                'ok' => count($brokenLinks['broken']) === 0,
                'detail' => count($brokenLinks['broken']) === 0
                    ? 'All ' . $brokenLinks['found'] . ' internal links checked across ' . $brokenLinks['checked'] . ' published posts/pages'
                    : count($brokenLinks['broken']) . ' broken link(s) across ' . $brokenLinks['checked'] . ' published posts/pages',
                'fix' => null,
            ],
        ];

        $allOk = collect($checks)->every(fn ($check) => $check['ok']);

        return view('admin.seo-health.index', compact('site', 'settings', 'checks', 'allOk', 'brokenLinks', 'missingAltCount'));
    }

    /**
     * Apply a one-click fix.
     */
    public function fix(Request $request, string $action)
    {
        $site = app('CurrentSite');

        if (!$site) {
            return back()->with('error', 'No site is resolved for this request.');
        }

        $settings = SiteSetting::forSite($site);

        switch ($action) {
            case 'enable-sitemap':
                $settings->enable_xml_sitemap = true;
                $settings->save();

                return back()->with('success', 'XML sitemap enabled.');

            case 'favicon-from-logo':
                if (empty($settings->logo_url)) {
                    return back()->with('error', 'No logo is configured — set one in Settings first.');
                }

                $settings->favicon_url = $settings->logo_url;
                $settings->save();

                return back()->with('success', 'Favicon set from the site logo.');

            case 'og-from-logo':
                if (empty($settings->logo_url)) {
                    return back()->with('error', 'No logo is configured — set one in Settings first.');
                }

                $settings->og_image = $settings->logo_url;
                $settings->save();

                return back()->with('success', 'Default OG image set from the site logo.');

            default:
                abort(404);
        }
    }
}
