@php
    try {
        $site = \App\Models\Site::first();
        $settings = \App\Models\SiteSetting::forSite($site);
        $theme_slug = $site->theme ?? 'rosetta';
        $headerMenu = \App\Models\Menu::forLocation('header', $site);
        $footerMenu = \App\Models\Menu::forLocation('footer', $site);
    } catch (\Throwable $e) {
        $site = null;
        $settings = new \App\Models\SiteSetting();
        $theme_slug = 'rosetta';
        $headerMenu = null;
        $footerMenu = null;
    }

    $layout = view()->exists('themes.' . $theme_slug . '.layout')
        ? 'themes.' . $theme_slug . '.layout'
        : 'themes.rosetta.layout';

    $theme_options = [];
    $theme_preview = false;
    $preview = false;
    $seo = [
        'title' => 'Page not found',
        'description' => 'The requested page could not be found.',
        'robots' => 'noindex, follow',
        'canonical' => url()->current(),
    ];
@endphp
@extends($layout)

@section('content')
    <div class="hero">
        <div class="container" style="text-align: center;">
            <p style="font-size: 0.85rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--secondary-color);">
                Error 404
            </p>
            <h1 style="margin-top: 0.5rem;">Page not found</h1>
            <p style="font-size: 1.15rem; margin-top: 0.75rem;">
                The page you are looking for may have moved, been unpublished, or never existed.
            </p>
            <div style="margin-top: 1.75rem; display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ url('/') }}" class="btn btn-primary">Back to home</a>
                <a href="{{ url('/blog') }}" class="btn btn-outline">Browse the blog</a>
            </div>
        </div>
    </div>

    <section style="padding: 3rem 0 4rem;">
        <div class="container" style="max-width: 560px;">
            <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">Looking for something specific?</h2>
            <form action="{{ url('/search') }}" method="GET" style="display: flex; gap: 0.5rem;">
                <label for="error-search" class="visually-hidden">Search</label>
                <input id="error-search" type="search" name="q" value="{{ request('q') }}"
                       placeholder="Search the site..."
                       style="flex: 1; padding: 0.65rem 0.85rem; border: 1px solid var(--border-color); border-radius: 6px;">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </section>
@endsection
