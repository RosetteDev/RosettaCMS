@extends('themes.photography.layout')

@section('content')
    <section class="section" style="padding-top:4rem;">
        <div class="container">
            <div class="section-title">
                <h1>{{ $category ? 'Category: ' . $category->name : 'Journal' }}</h1>
                <p>{{ $category?->description ?? 'All stories.' }}</p>
            </div>
            @if($posts->isNotEmpty())
                <div class="portfolio-grid">
                    @foreach($posts as $post)
                        <a href="{{ url('/posts/' . $post->slug) }}" class="portfolio-item">
                            @if($post->featured_image_url)
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="lightbox-trigger" data-lightbox="{{ $post->featured_image_url }}" data-lightbox-title="{{ $post->title }}">
                            @endif
                            <div class="overlay">
                                <span>{{ $post->published_at?->format('M Y') ?? $post->created_at->format('M Y') }}</span>
                                <h3>{{ $post->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div style="margin-top:2.5rem;text-align:center;">{{ $posts->withQueryString()->links() }}</div>
            @else
                <p style="text-align:center;color:#94a3b8;">No stories found.</p>
            @endif
        </div>
    </section>
@endsection
