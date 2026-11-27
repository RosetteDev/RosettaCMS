@extends('themes.magazine.layout')

@section('content')
    <section class="container" style="padding:3rem 0;">
        <header style="border-bottom:3px solid #111111;padding-bottom:1.25rem;margin-bottom:2rem;">
            <h1 style="font-size:2.5rem;">{{ $category ? 'Category: ' . $category->name : 'Latest' }}</h1>
            <p style="color:var(--secondary-color);font-family:-apple-system,sans-serif;">{{ $category?->description ?? 'All stories.' }}</p>
        </header>
        @if($posts->isNotEmpty())
            <div class="mag-stories" style="padding:0;">
                @foreach($posts as $post)
                    <article class="mag-story">
                        <div class="kicker">{{ $post->categories->first()->name ?? 'Story' }}</div>
                        <h3><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h3>
                        <p>{{ Str::limit($post->plain_excerpt, 150) }}</p>
                        <div class="post-meta" style="margin-top:0.75rem;">{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</div>
                    </article>
                @endforeach
            </div>
            <div style="text-align:center;margin-top:2rem;">{{ $posts->withQueryString()->links() }}</div>
        @else
            <p style="text-align:center;color:var(--secondary-color);">No stories found.</p>
        @endif
    </section>
@endsection
