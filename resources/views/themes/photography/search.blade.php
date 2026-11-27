@extends('themes.photography.layout')

@section('content')
    <section class="section" style="padding-top:4rem;">
        <div class="container">
            <div class="section-title"><h1>Search</h1></div>
            <form action="{{ url('/search') }}" method="GET" style="max-width:560px;margin:0 auto 2rem;display:flex;gap:0.5rem;">
                <input type="text" name="q" value="{{ $query }}" placeholder="Search stories..." required style="flex:1;padding:0.75rem;border:1px solid #1e293b;background:#0f172a;color:#f1f5f9;border-radius:0.5rem;">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
            @if($posts->isNotEmpty())
                <p style="text-align:center;color:#94a3b8;font-family:-apple-system,sans-serif;margin-bottom:1.5rem;">Found {{ $posts->total() }} result{{ $posts->total() === 1 ? '' : 's' }}.</p>
                <div class="portfolio-grid">
                    @foreach($posts as $post)
                        <a href="{{ url('/posts/' . $post->slug) }}" class="portfolio-item">
                            @if($post->featured_image_url)
                                <img loading="lazy" decoding="async" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="lightbox-trigger" data-lightbox="{{ $post->featured_image_url }}" data-lightbox-title="{{ $post->title }}">
                            @endif
                            <div class="overlay">
                                <span>{{ $post->published_at?->format('M Y') ?? $post->created_at->format('M Y') }}</span>
                                <h3>{{ $post->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div style="margin-top:2rem;text-align:center;">{{ $posts->withQueryString()->links() }}</div>
            @else
                <div style="text-align:center;padding:2rem 0;">
                    <h2>No results found</h2>
                    <p style="color:#94a3b8;">Try different keywords.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
