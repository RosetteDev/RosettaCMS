@extends('themes.blog.layout')

@section('content')
    <header class="post-header-hero">
        <div class="container">
            <h1>{{ $settings->resolved_site_title ?? ($site->name ?? 'Blog') }}</h1>
            <p>{{ $settings->resolved_site_description ?? 'Thoughts, notes and essays.' }}</p>
        </div>
    </header>

    <style>
        .blog-layout { display: grid; grid-template-columns: minmax(0, 1fr) 260px; gap: 2.5rem; align-items: start; }
        .blog-sidebar { position: sticky; top: 2rem; }
        .blog-sidebar .widget-area { margin-bottom: 1.5rem; }
        .blog-sidebar .about-widget { background: #f5f0e8; border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1.25rem; font-family: -apple-system, sans-serif; font-size: 0.9rem; color: #57534e; }
        .blog-sidebar .about-widget h3 { font-family: Georgia, serif; font-size: 1.1rem; color: #292524; margin-bottom: 0.5rem; }
        @media (max-width: 900px) { .blog-layout { grid-template-columns: 1fr; } .blog-sidebar { position: static; } }
    </style>

    <div class="container blog-layout" style="padding-top:2.5rem;">
        <div class="blog-main">
            <div class="post-list">
                @if($posts->isNotEmpty())
                    @foreach($posts as $post)
                        <article class="post-item">
                            @if($post->featured_image_url)
                                <a href="{{ url('/posts/' . $post->slug) }}"><img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}"></a>
                            @endif
                            <div class="meta">{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }} &middot; By {{ $post->author->name ?? 'Author' }}</div>
                            <h2><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h2>
                            <p>{{ Str::limit($post->plain_excerpt, 240) }}</p>
                            <a href="{{ url('/posts/' . $post->slug) }}" class="btn btn-primary">Read more</a>
                        </article>
                    @endforeach
                    <div style="text-align:center;margin-top:1.5rem;"><a href="{{ url('/blog') }}" class="btn btn-accent">View all posts</a></div>
                @else
                    <p style="text-align:center;color:var(--secondary-color);padding:2rem 0;">No posts yet. Check back soon.</p>
                @endif
            </div>
        </div>
        <aside class="blog-sidebar">
            @include('themes._shared.widget-area', ['location' => 'sidebar'])
            <div class="about-widget">
                <h3>About</h3>
                <p>{{ $settings->resolved_site_description ?? ($site->description ?? 'A clean, typography-focused blog.') }}</p>
            </div>
        </aside>
    </div>
@endsection
