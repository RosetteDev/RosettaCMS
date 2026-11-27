@extends('themes.elegant::layouts.app')

@section('content')
<!-- Hero Section -->
<div class="hero">
    <div class="container">
        <h1>{{ $post->title ?? 'Post' }}</h1>
        <p style="font-size: 1.15rem; margin-top: 0.5rem;">
            By {{ $post->author->name ?? 'Author' }} on {{ $post->published_at?->format('F j, Y') ?? now()->format('F j, Y') }}
        </p>
    </div>
</div>

<!-- Post Content -->
<section style="padding: 4rem 0;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            @if($post->featured_image)
                <img src="{{ $post->featured_image->url ?? '' }}" alt="{{ $post->title }}" style="width: 100%; border-radius: 0.5rem; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            @endif

            <div class="post-content" style="line-height: 1.8; font-size: 1.125rem;">
                @php
                    $content = $post->rendered_content ?? '';

                    if (function_exists('apply_filters')) {
                        $content = apply_filters('the_content', $content);
                    }

                    // Convert YouTube URLs to embedded iframes (handles watch, shorts, and embed URLs)
                    echo preg_replace_callback(
                        '/https?:\/\/(?:www\.)?youtube\.com\/(?:watch\?v=|shorts\/|embed\/)([a-zA-Z0-9_-]{11})/',
                        function($matches) {
                            $videoId = $matches[1];
                            return '<div class="youtube-embed" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:0.5rem;margin:1rem 0;"><iframe src="https://www.youtube.com/embed/' . $videoId . '" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                        },
                        $content
                    );
                @endphp
            </div>

            <!-- Categories and Tags -->
            @if($post->categories()->count() > 0 || $post->tags()->count() > 0)
                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color, #e5e7eb);">
                    @if($post->categories()->count() > 0)
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: var(--text-color);">Categories:</strong>
                        @foreach($post->categories as $category)
                            <a href="{{ url('/categories/' . $category->slug) }}" class="tag">{{ $category->name }}</a>
                        @endforeach
                    </div>
                    @endif

                    @if($post->tags()->count() > 0)
                    <div>
                        <strong style="color: var(--text-color);">Tags:</strong>
                        @foreach($post->tags as $tag)
                            <a href="{{ url('/tags/' . $tag->slug) }}" class="tag">{{ $tag->name }}</a>
                        @endforeach
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</section>

<!-- Author Bio -->
@if($post->author)
<section style="padding: 3rem 0; background-color: var(--bg-color);">
    <div class="container" style="max-width: 800px; margin: 0 auto; text-align: center;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 1.5rem;">
            <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden;">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($post->author->name) }}&background={{ substr(md5($post->author->email), 0, 6) }}&color=fff" alt="{{ $post->author->name }}">
            </div>
            <div style="text-align: left;">
                <h3 style="margin: 0 0 0.5rem; color: var(--text-color);">{{ $post->author->name }}</h3>
                <p style="margin: 0; color: var(--secondary-color);">Author</p>
            </div>
        </div>

        @if($post->author->bio)
            <p style="margin-top: 1.5rem; color: var(--secondary-color); line-height: 1.6;">
                {{ $post->author->bio }}
            </p>
        @endif
    </div>
</section>
@endif

<!-- Related Posts -->
@if($relatedPosts->count() > 0)
<section style="padding: 4rem 0;">
    <div class="container" style="max-width: 800px; margin: 0 auto;">
        <h2 style="font-size: 2rem; margin-bottom: 2rem; color: var(--text-color);">Related Posts</h2>

        <div class="posts-grid">
            @foreach($relatedPosts->take(3) as $relatedPost)
            <div class="card post-item">
                @if($relatedPost->featured_image)
                <img src="{{ $relatedPost->featured_image->url ?? '' }}" alt="{{ $relatedPost->title }}" class="card-img">
                @endif
                <div class="card-content">
                    <div class="post-meta" style="font-size: 0.875rem;">
                        {{ $relatedPost->published_at?->format('F j, Y') ?? now()->format('F j, Y') }}
                    </div>
                    <h3 style="margin-bottom: 0.75rem;">
                        <a href="{{ url('/posts/' . $relatedPost->slug) }}" style="color: var(--text-color); text-decoration: none;">{{ $relatedPost->title }}</a>
                    </h3>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
