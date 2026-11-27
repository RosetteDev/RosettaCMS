<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Language;
use App\Models\Site;

class SetActiveLanguage
{
    /**
     * Resolve the active language for the request.
     *
     * Priority: `?lang=xx` query param (sticky via session) → session value →
     * the site's default language. Sets the app locale and a `$activeLanguage`
     * variable available to views.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $code = $request->query('lang') ?? session('active_language');

        $language = null;

        if ($code) {
            $candidate = Language::where('code', $code)->where('is_active', true)->first();

            if ($candidate) {
                $language = $candidate;
                session(['active_language' => $code]);
            }
        }

        if (!$language) {
            $site = Site::where('domain', $request->getHost())->first() ?? Site::first();
            $language = Language::defaultLanguageFor($site);
        }

        if ($language) {
            app()->setLocale($language->code);
            $request->attributes->set('activeLanguage', $language);
        } else {
            $request->attributes->set('activeLanguage', null);
        }

        return $next($request);
    }
}
