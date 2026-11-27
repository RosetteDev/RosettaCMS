@extends('themes.blog.layout')

@section('content')
    <div class="container" style="padding-top:2.5rem;">
        <header class="post-header-hero" style="padding:0 0 1.5rem;border-bottom:1px solid var(--border-color);">
            <h1>{{ $category ? 'Category: ' . $category->name : 'Blog' }}</h1>
            <p>{{ $category?->description ?? 'All posts.' }}</p>
        </header>

        <style>
            .blog-layout { display: grid; grid-template-columns: minmax(0, 1fr) 260px; gap: 2.5rem; align-items: start; }
            .blog-sidebar { position: sticky; top: 2rem; }
            .blog-sidebar .widget-area { margin-bottom: 1.5rem; }
            .blog-sidebar .about-widget { background: #f5f0e8; border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem; font-family: -apple-system, sans-serif; font-size: 0.9rem; color: #57534e; }
            .blog-sidebar .about-widget h3 { font-family: Georgia, serif; font-size: 1.1rem; color: #292524; margin-bottom: 0.5rem; }
            @media (max-width: 900px) { .blog-layout { grid-template-columns: 1fr; } .blog-sidebar { position: static; } }
        </style>

        <div class="blog-layout" style="padding-top:2rem;">
            <div class="post-list">
                @if($posts->isNotEmpty())
                    @foreach($posts as $post)
                        <article class="post-item">
                            @if($post->featured_image_url)
                                <a href="{{ url('/posts/' . $post->slug) }}"><img loading="lazy" decoding="async" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}"></a>
                            @endif
                            <div class="meta">{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</div>
                            <h2><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h2>
                            <p>{{ Str::limit($post->plain_excerpt, 200) }}</p>
                        </article>
                    @endforeach
                    <div style="text-align:center;margin-top:1.5rem;">{{ $posts->withQueryString()->links() }}</div>
                @else
                    <p style="text-align:center;color:var(--secondary-color);padding:2rem 0;">No posts found.</p>
                @endif
            </div>
            <aside class="blog-sidebar">
                @include('themes._shared.widget-area', ['location' => 'sidebar'])
                <div class="about-widget">
                    <h3>About</h3>
                    <p>{{ $settings->resolved_site_description ?? ($site->description ?? 'A clean, typography-focused blog.') }}</p>
                </div>
            </aside>
        </div>
    </div>
@endsection
