@extends('themes.photography.layout')

@section('content')
    <article class="featured-single">
        @if($post->featured_image_url)
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
        @endif
        <div class="container caption">
            <div class="post-meta" style="justify-content:center;">
                <span>{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</span>
                <span>By {{ $post->author->name ?? 'Author' }}</span>
            </div>
            <h1>{{ $post->title }}</h1>
        </div>
    </article>

    <section class="section" style="padding-top:0;">
        <div class="container" style="max-width:760px;">
            <div class="post-content">{!! $post->rendered_content !!}</div>

            @if($post->categories()->count() > 0 || $post->tags()->count() > 0)
                <div style="margin-top:2rem;padding-top:1.5rem;border-top:1px solid var(--border-color);">
                    @foreach($post->categories as $category)
                        <a href="{{ url('/categories/' . $category->slug) }}" style="display:inline-block;margin:0 0.35rem 0.35rem 0;padding:0.35rem 0.9rem;background:#1e293b;border-radius:999px;font-family:-apple-system,sans-serif;font-size:0.75rem;color:#f8fafc;">{{ $category->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
