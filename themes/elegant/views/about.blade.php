@extends('themes.elegant::layouts.app')

@section('content')
<!-- Hero Section -->
<div class="hero">
    <div class="container">
        <h1>{{ $page->title ?? 'About Us' }}</h1>
        <p style="font-size: 1.2rem; margin-top: 0.5rem;">Learn more about who we are and what we do</p>
    </div>
</div>

<!-- About Content -->
<section style="padding: 4rem 0;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            @if($page && $page->rendered_content)
            <div class="post-content" style="line-height: 1.8; font-size: 1.125rem;">
                @php
                    $content = $page->rendered_content;

                    if (function_exists('apply_filters')) {
                        $content = apply_filters('the_content', $content);
                    }

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
            @else
            <div class="post-content" style="line-height: 1.8; font-size: 1.125rem;">
                <p>Welcome to our site. This is the about page.</p>
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
