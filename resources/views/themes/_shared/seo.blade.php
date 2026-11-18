@php
    $seo = $seo ?? [];
    $siteName = $seo['site_name'] ?? ($settings->site_title ?? ($site->name ?? 'Rosetta CMS'));
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
    <link rel="icon" href="{{ $favicon }}">
@endif

{{-- Feeds and sitemap --}}
@if(($settings->enable_xml_sitemap ?? true))
    <link rel="sitemap" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">
@endif
<link rel="alternate" type="application/rss+xml" title="{{ $siteName }} Feed" href="{{ url('/feed') }}">
