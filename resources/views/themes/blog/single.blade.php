@extends('themes.blog.layout')

@section('content')
    <header class="single-post-hero">
        <div class="container">
            <div class="meta">{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }} &middot; By {{ $post->author->name ?? 'Author' }}</div>
            <h1>{{ $post->title }}</h1>
        </div>
    </header>

    <article class="container single-body">
        @if($post->featured_image_url)
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
        @endif
        <div class="post-content">{!! $post->rendered_content !!}</div>

        @if($post->author)
            <div class="author-box" style="margin-top:2rem;padding:1.5rem;background:#f5f0e8;border-radius:0.5rem;display:flex;gap:1rem;align-items:center;">
                <div style="width:56px;height:56px;border-radius:50%;background:#0f766e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;font-family:-apple-system,sans-serif;flex-shrink:0;">
                    {{ strtoupper(substr($post->author->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <div style="font-family:-apple-system,sans-serif;font-size:0.75rem;letter-spacing:0.08em;text-transform:uppercase;color:#a8a29e;">Written by</div>
                    <div style="font-weight:700;font-size:1.1rem;">{{ $post->author->name }}</div>
                    <p style="margin:0.35rem 0 0;color:#57534e;font-family:-apple-system,sans-serif;font-size:0.9rem;">Sharing notes and essays on the web, writing and craft.</p>
                </div>
            </div>
        @endif

        @if($post->categories()->count() > 0 || $post->tags()->count() > 0)
            <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border-color);">
                @foreach($post->categories as $category)
                    <a href="{{ url('/categories/' . $category->slug) }}" style="display:inline-block;margin:0 0.35rem 0.35rem 0;padding:0.3rem 0.8rem;background:#f0fdfa;border-radius:20px;font-family:-apple-system,sans-serif;font-size:0.75rem;color:#0f766e;">{{ $category->name }}</a>
                @endforeach
            </div>
        @endif
    </article>

    @if(isset($relatedPosts) && $relatedPosts->isNotEmpty())
        <section style="border-top:1px solid var(--border-color);padding:2.5rem 0;">
            <div class="container">
                <h2 style="font-size:1.4rem;margin-bottom:1.25rem;">More from the blog</h2>
                <ul style="list-style:none;padding:0;">
                    @foreach($relatedPosts as $related)
                        <li style="padding:0.75rem 0;border-bottom:1px solid var(--border-color);">
                            <a href="{{ url('/posts/' . $related->slug) }}" style="font-size:1.05rem;">{{ $related->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
