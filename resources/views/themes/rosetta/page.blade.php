@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>{{ $page->title ?? 'Page' }}</h1>
            @if($page?->excerpt)
                <p style="font-size: 1.15rem; margin-top: .75rem;">{{ $page->excerpt }}</p>
            @endif
        </div>
    </div>

    <!-- Page Content -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
                <div class="page-content post-content">
                    @php
                        $content = $page?->rendered_content ?? '';

                        // Convert YouTube URLs to embedded iframes (handles watch, shorts, and embed URLs)
                        echo preg_replace_callback(
                            '/https?:\/\/(?:www\.)?youtube\.com\/(?:watch\?v=|shorts\/|embed\/)([a-zA-Z0-9_-]{11})/',
                            function ($matches) {
                                $videoId = $matches[1];
                                return '<div class="youtube-embed" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;"><iframe src="https://www.youtube.com/embed/' . $videoId . '" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                            },
                            $content
                        );
                    @endphp
                </div>
            </div>
        </div>
    </section>

    @if($page && $page->featured_image_url)
        <section style="padding-bottom: 3rem;">
            <div class="container" style="max-width: 800px; margin: 0 auto;">
                <img src="{{ $page->featured_image_url }}" alt="{{ $page->title }}" style="width:100%;border-radius:.5rem;">
            </div>
        </section>
    @endif
@endsection
