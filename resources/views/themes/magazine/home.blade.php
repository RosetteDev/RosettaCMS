@extends('themes.magazine.layout')

@section('content')
    @php
        $tickerItems = $posts->take(4)->pluck('title')->filter()->values();
        $categories = \App\Models\Category::with(['posts' => function ($q) { $q->where('status', 'published')->latest('published_at'); }])->whereHas('posts', function ($q) { $q->where('status', 'published'); })->take(3)->get();
    @endphp

    <header class="mag-masthead">
        <div class="container">
            <h1>{{ $settings->resolved_site_title ?? ($site->name ?? 'Magazine') }}</h1>
            <p>{{ $settings->resolved_site_description ?? 'Stories worth reading' }}</p>
        </div>
    </header>

    @if($tickerItems->isNotEmpty())
        <div class="ticker" aria-label="Breaking news">
            <span class="ticker-label">Breaking</span>
            <div class="ticker-track">
                <div class="ticker-content">
                    @foreach($tickerItems as $item)
                        <span>{{ $item }}</span><span class="ticker-sep">&#9679;</span>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($posts->isNotEmpty())
        @php $featured = $posts->first(); @endphp
        <section class="container" style="padding-top:2rem;">
            <div class="mag-feature">
                <a href="{{ url('/posts/' . $featured->slug) }}">
                    @if($featured->featured_image_url)
                        <img loading="lazy" decoding="async" src="{{ $featured->featured_image_url }}" alt="{{ $featured->title }}">
                    @endif
                </a>
                <div>
                    <div class="kicker">Featured</div>
                    <h2><a href="{{ url('/posts/' . $featured->slug) }}">{{ $featured->title }}</a></h2>
                    <p>{{ Str::limit($featured->plain_excerpt, 200) }}</p>
                    <div class="byline">By {{ $featured->author->name ?? 'Author' }} &middot; {{ $featured->published_at?->format('F j, Y') ?? $featured->created_at->format('F j, Y') }}</div>
                    <div style="margin-top:1.25rem;"><a href="{{ url('/posts/' . $featured->slug) }}" class="btn btn-primary">Read the story</a></div>
                </div>
            </div>
        </section>

        @if($posts->count() > 1)
            <section class="container">
                <div class="mag-stories">
                    @foreach($posts->slice(1, 3) as $post)
                        <article class="mag-story">
                            <div class="kicker">{{ $post->categories->first()->name ?? 'Story' }}</div>
                            <h3><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h3>
                            <p>{{ Str::limit($post->plain_excerpt, 140) }}</p>
                            <div class="post-meta" style="margin-top:0.75rem;">{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if($categories->isNotEmpty())
            <section class="container" style="padding-bottom:2rem;">
                <h2 style="font-size:1.6rem;border-bottom:3px solid #111111;padding-bottom:0.75rem;margin-bottom:1.5rem;">Sections</h2>
                <div class="mag-stories" style="padding:0;">
                    @foreach($categories as $category)
                        <article class="mag-story">
                            <div class="kicker">{{ $category->name }}</div>
                            <h3><a href="{{ url('/categories/' . $category->slug) }}">{{ $category->description ?? 'Explore this section' }}</a></h3>
                            @if($category->posts->isNotEmpty())
                                <p>{{ Str::limit($category->posts->first()->plain_excerpt, 120) }}</p>
                                <div class="post-meta" style="margin-top:0.75rem;">{{ $category->posts->count() }} stor{{ $category->posts->count() === 1 ? 'y' : 'ies' }}</div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div style="text-align:center;padding-bottom:2rem;"><a href="{{ url('/blog') }}" class="btn btn-primary">View all stories</a></div>
    @else
        <section class="container" style="text-align:center;padding:3rem 0;">
            <h2>No stories yet</h2>
            <p style="color:var(--secondary-color);">Check back soon for new stories.</p>
        </section>
    @endif

    <style>
        .ticker { display: flex; align-items: center; background: #dc2626; color: #fff; padding: 0.5rem 0; overflow: hidden; font-family: -apple-system, sans-serif; }
        .ticker-label { background: #111111; color: #fff; font-weight: 700; font-size: 0.72rem; letter-spacing: 0.12em; text-transform: uppercase; padding: 0.45rem 1rem; margin-right: 1rem; flex-shrink: 0; align-self: stretch; display: flex; align-items: center; }
        .ticker-track { overflow: hidden; flex: 1; }
        .ticker-content { display: inline-flex; gap: 1.5rem; white-space: nowrap; animation: ticker-scroll 22s linear infinite; padding-left: 1.5rem; }
        .ticker-content span { font-size: 0.82rem; }
        .ticker-sep { opacity: 0.6; }
        @keyframes ticker-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-50%); } }
        @media (prefers-reduced-motion: reduce) { .ticker-content { animation: none; } }
    </style>
@endsection
