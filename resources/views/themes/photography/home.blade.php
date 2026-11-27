@extends('themes.photography.layout')

@section('content')
    <section class="hero-portfolio">
        <div class="container hero-portfolio-inner">
            <h1>{{ $settings->resolved_site_title ?? ($site->name ?? 'Portfolio') }}</h1>
            <p>{{ $settings->resolved_site_description ?? 'Capturing light, stories and moments.' }}</p>
            <div class="hero-cta">
                <a href="{{ url('/search') }}" class="primary">Explore the work</a>
                <a href="{{ url('/about') }}" class="ghost">About</a>
            </div>
        </div>
    </section>

    @if($posts->isNotEmpty())
        @php
            $categories = \App\Models\Category::whereHas('posts', function ($q) { $q->where('status', 'published'); })->orderBy('name')->get();
        @endphp
        <section class="section">
            <div class="container">
                @if($categories->isNotEmpty())
                    <div class="filter-chips" style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;margin-bottom:1.5rem;">
                        <a href="{{ url('/') }}" class="chip active" style="font-family:-apple-system,sans-serif;font-size:0.78rem;padding:0.4rem 1rem;border-radius:999px;border:1px solid #f59e0b;color:#f59e0b;">All</a>
                        @foreach($categories as $category)
                            <a href="{{ url('/categories/' . $category->slug) }}" class="chip" style="font-family:-apple-system,sans-serif;font-size:0.78rem;padding:0.4rem 1rem;border-radius:999px;border:1px solid #1e293b;color:#94a3b8;">{{ $category->name }}</a>
                        @endforeach
                    </div>
                @endif
                <div class="section-title">
                    <h2>Latest work</h2>
                    <p>Recently published stories</p>
                </div>
                <div class="portfolio-grid">
                    @foreach($posts->take(6) as $post)
                        <a href="{{ url('/posts/' . $post->slug) }}" class="portfolio-item">
                            @if($post->featured_image_url)
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="lightbox-trigger" data-lightbox="{{ $post->featured_image_url }}" data-lightbox-title="{{ $post->title }}">
                            @else
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#1e293b;">
                                    <span style="font-family:-apple-system,sans-serif;color:#475569;font-size:0.8rem;letter-spacing:0.1em;text-transform:uppercase;">No image</span>
                                </div>
                            @endif
                            <div class="overlay">
                                <span>{{ $post->published_at?->format('M Y') ?? $post->created_at->format('M Y') }}</span>
                                <h3>{{ $post->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section" style="padding-top:0;">
        <div class="container" style="text-align:center;">
            <a href="{{ url('/contact') }}" class="btn btn-primary">Get in touch</a>
        </div>
    </section>
@endsection
