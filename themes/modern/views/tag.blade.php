@extends('themes.modern::layouts.app')

@section('content')
<style>
    .theme-list-layout { display: grid; grid-template-columns: 1fr 300px; gap: 2.5rem; align-items: start; }
    @media (max-width: 900px) { .theme-list-layout { grid-template-columns: 1fr; } }
    .theme-post-card { background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; transition: transform 0.3s, box-shadow 0.3s; }
    .theme-post-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.1); }
    .theme-post-card-body { padding: 1.5rem 2rem; }
    .theme-post-card h2 { margin-bottom: 0.5rem; font-size: 1.5rem; }
    .theme-post-card h2 a { color: var(--text-color); text-decoration: none; }
    .theme-post-card h2 a:hover { color: var(--primary-color); }
    .theme-post-card .theme-post-meta { color: var(--secondary-color); font-size: 0.95rem; margin-bottom: 0.75rem; font-style: italic; }
    .theme-post-card .theme-post-excerpt { color: var(--text-color); margin-bottom: 1rem; line-height: 1.7; }
    .theme-post-card .theme-post-link { color: var(--primary-color); text-decoration: none; font-weight: 500; }
    .theme-post-card .theme-post-link:hover { text-decoration: underline; }
    .pagination { margin-top: 2rem; }
</style>

<div class="container" style="padding-top: 3rem; padding-bottom: 3rem;">
    <div class="theme-list-layout">
        <div>
            <div class="archive-header mb-4">
                <h1 class="archive-title">Tag: {{ $tag->name }}</h1>
                @if($tag->description)
                    <p class="search-count">{{ $tag->description }}</p>
                @endif
            </div>

            @if($posts->count())
                @foreach($posts as $post)
                <article class="theme-post-card">
                    @if($post->featured_image)
                        <img src="{{ $post->featured_image->url ?? '' }}" alt="{{ $post->title }}" style="width: 100%; height: 240px; object-fit: cover;">
                    @endif
                    <div class="theme-post-card-body">
                        <h2>
                            <a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a>
                        </h2>
                        <div class="theme-post-meta">
                            {{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}
                        </div>
                        <p class="theme-post-excerpt">{{ Str::limit($post->excerpt ?? strip_tags($post->content), 200) }}</p>
                        <a href="{{ url('/posts/' . $post->slug) }}" class="theme-post-link">Read More →</a>
                    </div>
                </article>
                @endforeach

                <div class="pagination">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="text-center" style="padding: 3rem 0;">
                    <p style="font-size: 1.25rem; color: var(--secondary-color); margin-bottom: 1.5rem;">No posts found{{ ' in this tag' }}.</p>
                    <a href="{{ url('/') }}" class="btn">Go Back Home</a>
                </div>
            @endif
        </div>

        <div>
            @include('themes.modern::partials.sidebar')
        </div>
    </div>
</div>
@endsection
