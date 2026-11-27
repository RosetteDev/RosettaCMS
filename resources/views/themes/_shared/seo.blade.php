@php
    $seo = $seo ?? [];
    $siteName = $seo['site_name'] ?? ($settings->resolved_site_title ?? ($site->name ?? 'Rosetta CMS'));
    $seoTitle = $seo['title'] ?? ($title ?? $siteName);
    $seoDescription = $seo['description'] ?? ($description ?? '');
    $canonical = $seo['canonical'] ?? url()->current();
    $robots = $seo['robots'] ?? null;
    $image = $seo['image'] ?? null;
    $ogType = $seo['type'] ?? 'website';
    $favicon = $settings->favicon_url ?? null;
@endphp

<title>{{ $seoTitle }}</title>

@if($seoDescription !== '')
    <meta name="description" content="{{ $seoDescription }}">
@endif

@if(!empty($seo['keywords']))
    <meta name="keywords" content="{{ $seo['keywords'] }}">
@endif

@if($robots)
    <meta name="robots" content="{{ $robots }}">
@endif

@if($canonical)
    <link rel="canonical" href="{{ $canonical }}">
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
@if($image)
    <meta property="og:image" content="{{ $image }}">
@endif
@if(!empty($seo['published_time']))
    <meta property="article:published_time" content="{{ $seo['published_time'] }}">
@endif
@if(!empty($seo['modified_time']))
    <meta property="article:modified_time" content="{{ $seo['modified_time'] }}">
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
@if($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif

@if($favicon)
    <link rel="icon" href="{{ \App\Facades\CDN::cdnUrl($favicon) }}">
@endif

{{-- Feeds and sitemap --}}
@if(($settings->enable_xml_sitemap ?? true))
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">
@endif
<link rel="alternate" type="application/rss+xml" title="{{ $siteName }} Feed" href="{{ url('/feed') }}">

{{-- JSON-LD Structured Data --}}
@php
    $jsonLd = [];
    
    // Website schema for homepage
    if (request()->is('/') || request()->is('home')) {
        $jsonLd['@context'] = 'https://schema.org';
        $jsonLd['@type'] = 'Website';
        $jsonLd['name'] = $siteName;
        $jsonLd['url'] = url('/');
        
        if ($seoDescription) {
            $jsonLd['description'] = $seoDescription;
        }
        
        $logoUrl = $settings->logo_url ?? null;
        if ($logoUrl) {
            $jsonLd['logo'] = $logoUrl;
            $jsonLd['image'] = $logoUrl;
        }
    }
    // Article schema for single posts/pages
    elseif (isset($post) && $post instanceof \App\Models\Post) {
        $jsonLd['@context'] = 'https://schema.org';
        $jsonLd['@type'] = 'Article';
        $jsonLd['headline'] = $post->title;
        $jsonLd['description'] = $post->excerpt ?: $seoDescription;
        $jsonLd['datePublished'] = $post->published_at?->toIso8601String();
        $jsonLd['dateModified'] = $post->updated_at?->toIso8601String();
        $jsonLd['url'] = $post->url;
        
        // Author
        if ($post->author) {
            $jsonLd['author'] = [
                '@type' => 'Person',
                'name' => $post->author->name,
                'url' => $post->author->url
            ];
        }
        
        // Featured image
        if ($post->featured_image) {
            $jsonLd['image'] = $post->featured_image;
        }
        
        // Categories
        if ($post->categories->count() > 0) {
            $jsonLd['articleSection'] = $post->categories->pluck('name')->first();
        }
        
        // Keywords
        if (!empty($seo['keywords'])) {
            $jsonLd['keywords'] = $seo['keywords'];
        }
    }
    // LocalBusiness schema for contact/about pages
    elseif (request()->is('contact') || request()->is('about')) {
        $jsonLd['@context'] = 'https://schema.org';
        $jsonLd['@type'] = 'LocalBusiness';
        $jsonLd['name'] = $siteName;
        $jsonLd['url'] = url('/');
        
        if ($seoDescription) {
            $jsonLd['description'] = $seoDescription;
        }
        
        // Address from site settings
        if (!empty($settings->address)) {
            $jsonLd['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->address,
                'addressLocality' => $settings->city ?? '',
                'addressRegion' => $settings->state ?? '',
                'postalCode' => $settings->zip ?? '',
                'addressCountry' => $settings->country ?? 'US'
            ];
        }
        
        // Phone
        if (!empty($settings->phone)) {
            $jsonLd['telephone'] = $settings->phone;
        }
    }
@endphp

@if(!empty($jsonLd))
    <script type="application/ld+json">{{ json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</script>
@endif
