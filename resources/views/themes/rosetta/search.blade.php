@extends('themes.rosetta.layout')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Search</h1>
            <p>{{ $query !== '' ? 'Results for “' . $query . '”' : 'Find content across the site' }}</p>
        </div>
    </div>

    <section style="padding: 2rem 0;">
        <div class="container">
            <form action="{{ url('/search') }}" method="GET" style="max-width: 640px; margin: 0 auto 2.5rem;">
                <div style="display:flex; gap:.5rem;">
                    <input type="text" name="q" value="{{ $query }}" class="form-control" placeholder="Search posts..." required>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>

            @if($posts->count() > 0)
                <p style="text-align:center; color: var(--secondary-color); margin-bottom: 2rem;">
                    Found {{ $posts->total() }} result{{ $posts->total() === 1 ? '' : 's' }}.
                </p>

                <div class="posts-grid">
                    @foreach($posts as $post)
                        <article class="post-item card">
                            @if($post->featured_image_url)
                                <img loading="lazy" decoding="async" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="card-img">
                            @endif
                            <div class="card-content">
                                <div class="post-meta">
                                    <span class="post-date">{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</span>
                                </div>
                                <h3 class="post-title">
                                    <a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <p class="post-excerpt">{{ Str::limit($post->plain_excerpt, 160) }}</p>
                                <a href="{{ url('/posts/' . $post->slug) }}" class="btn btn-primary">Read More</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div style="margin-top: 2rem;">
                    {{ $posts->withQueryString()->links() }}
                </div>
            @else
                <div style="text-align:center; padding: 3rem 0;">
                    <h2 style="margin-bottom: 1rem;">No results found</h2>
                    <p style="color: var(--secondary-color); margin-bottom: 1.5rem;">
                        We couldn't find anything matching your search. Try different keywords.
                    </p>
                    <a href="{{ url('/') }}" class="btn btn-primary">Back to Home</a>
                </div>
            @endif
        </div>
    </section>
@endsection
