@extends('themes.blog.layout')

@section('content')
    <div class="container post-list" style="padding-top:3rem;">
        <header class="post-header-hero" style="padding:0 0 2rem;border-bottom:1px solid var(--border-color);">
            <h1>Tag: {{ $tag->name ?? 'Tag' }}</h1>
            <p>{{ $tag?->description ?? 'Posts tagged with this.' }}</p>
        </header>
        @if($posts->isNotEmpty())
            @foreach($posts as $post)
                <article class="post-item">
                    <div class="meta">{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</div>
                    <h2><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h2>
                    <p>{{ Str::limit($post->plain_excerpt, 200) }}</p>
                </article>
            @endforeach
            <div style="text-align:center;margin-top:1rem;">{{ $posts->withQueryString()->links() }}</div>
        @else
            <p style="text-align:center;color:var(--secondary-color);padding:2rem 0;">No posts found for this tag.</p>
        @endif
    </div>
@endsection
