@php
    $seo = $seo ?? [];
    $siteName = $seo['site_name'] ?? ((isset($settings) && $settings ? $settings->resolved_site_title : null) ?? (isset($site) && $site ? $site->name : null) ?? 'Rosetta CMS');
    $seoTitle = $seo['title'] ?? ($title ?? $siteName);
    $seoDescription = $seo['description'] ?? ($description ?? '');
    $canonical = $seo['canonical'] ?? url()->current();
    $robots = $seo['robots'] ?? null;
    $image = $seo['image'] ?? (isset($settings) && $settings ? $settings->defaultOgImage() : null);
    $ogType = $seo['type'] ?? 'website';
    $favicon = (isset($settings) && $settings ? $settings->favicon_url : null) ?? null;
    $twitterHandle = $seo['twitter_handle'] ?? null;
    $verificationMeta = $seo['verification_meta'] ?? [];
    $ogLocale = $seo['og_locale'] ?? null;
    $hreflang = $seo['hreflang'] ?? [];
    $faq = $seo['faq'] ?? null;
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

{{-- Hreflang alternates --}}
@foreach($hreflang as $lang => $url)
    <link rel="alternate" hreflang="{{ $lang }}" href="{{ $url }}">
@endforeach

@if($canonical)
    <link rel="alternate" hreflang="x-default" href="{{ $canonical }}">
@endif

@if($twitterHandle)
    <meta name="twitter:site" content="{{ $twitterHandle }}">
@endif

@foreach($verificationMeta as $engine => $content)
    <meta name="{{ $engine === 'google' ? 'google-site-verification' : ($engine === 'bing' ? 'msvalidate.01' : $engine . '-site-verification') }}" content="{{ $content }}">
@endforeach

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $seoTitle }}">
@if($seoDescription !== '')
    <meta property="og:description" content="{{ $seoDescription }}">
@endif
<meta property="og:type" content="{{ $ogType }}">
<meta property="og:url" content="{{ $canonical }}">
@if($ogLocale)
    <meta property="og:locale" content="{{ $ogLocale }}">
@endif
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
@if($seoDescription !== '')
    <meta name="twitter:description" content="{{ $seoDescription }}">
@endif
@if($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif

@if($favicon)
    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $favicon }}">
@endif

{{-- Feeds and sitemap --}}
@if(!isset($settings) || !$settings || ($settings->enable_xml_sitemap ?? true))
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">
@endif
<link rel="alternate" type="application/rss+xml" title="{{ $siteName }} Feed" href="{{ url('/feed') }}">

{{-- JSON-LD Structured Data --}}
@php
    $schemas = [];

    // Website schema for homepage
    if (request()->is('/') || request()->is('home')) {
        $website = [
            '@context' => 'https://schema.org',
            '@type' => 'Website',
            'name' => $siteName,
            'url' => url('/'),
        ];

        if ($seoDescription) {
            $website['description'] = $seoDescription;
        }

        $logoUrl = (isset($settings) && $settings ? $settings->logo_url : null) ?? null;
        if ($logoUrl) {
            $website['logo'] = $logoUrl;
            $website['image'] = $logoUrl;
        }

        $schemas[] = $website;
    }
    // Article schema for single posts/pages
    elseif (isset($seo['post']) && $seo['post'] instanceof \App\Models\Post) {
        $post = $seo['post'];
        $article = [
            '@context' => 'https://schema.org',
            '@type' => $post->post_type === 'page' ? 'WebPage' : 'Article',
            'headline' => $post->title,
            'description' => $post->excerpt ?: $seoDescription,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'url' => $post->public_url,
        ];

        if ($post->post_type === 'post') {
            $article['articleSection'] = $post->categories->first()?->name;
        }

        // Author
        if ($post->author) {
            $article['author'] = [
                '@type' => 'Person',
                'name' => $post->author->name,
                'url' => $post->author->url
            ];
        }

        // Featured image
        if ($post->featured_image) {
            $article['image'] = $post->featured_image_url;
        }

        // Keywords
        if (!empty($seo['keywords'])) {
            $article['keywords'] = $seo['keywords'];
        }

        $schemas[] = $article;
    }
    // LocalBusiness schema for contact/about pages
    elseif (request()->is('contact') || request()->is('about')) {
        $business = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $siteName,
            'url' => url('/'),
        ];

        if ($seoDescription) {
            $business['description'] = $seoDescription;
        }

        if (isset($settings) && $settings && !empty($settings->address)) {
            $business['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->address,
                'addressLocality' => $settings->city ?? '',
                'addressRegion' => $settings->state ?? '',
                'postalCode' => $settings->zip ?? '',
                'addressCountry' => $settings->country ?? 'US'
            ];
        }

        if (isset($settings) && $settings && !empty($settings->phone)) {
            $business['telephone'] = $settings->phone;
        }

        $schemas[] = $business;
    }

    // BreadcrumbList for single posts/pages (and the homepage fallback)
    $breadcrumbItems = [];
    $post = $seo['post'] ?? null;
    if ($post instanceof \App\Models\Post) {
        $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')];
        if ($post->post_type === 'page') {
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 2, 'name' => $post->title, 'item' => $post->public_url];
        } else {
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => url('/blog')];
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => $post->public_url];
        }
    }

    if (count($breadcrumbItems) >= 2) {
        $schemas[] = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ];
    }

    // FAQPage when the post carries an FAQ (meta.faq = [{question, answer}, ...])
    if (is_array($faq) && count($faq) > 0) {
        $mainEntity = [];
        foreach ($faq as $item) {
            if (is_array($item) && !empty($item['question']) && !empty($item['answer'])) {
                $mainEntity[] = [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            }
        }

        if (count($mainEntity) > 0) {
            $schemas[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $mainEntity,
            ];
        }
    }
@endphp

@foreach($schemas as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endforeach
