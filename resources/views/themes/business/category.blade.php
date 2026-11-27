@extends('themes.business.layout')

@section('content')
    <section class="section" style="padding-top:3rem;">
        <div class="container">
            <div class="section-title">
                <h1>{{ $category ? 'Category: ' . $category->name : 'Blog' }}</h1>
                <p>{{ $category?->description ?? 'All posts from the site.' }}</p>
            </div>
            @if($posts->isNotEmpty())
                <div class="posts-grid">
                    @foreach($posts as $post)
                        <article class="card">
                            @if($post->featured_image_url)
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
                            @endif
                            <div class="card-body">
                                <div class="post-meta"><span>{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</span></div>
                                <h3><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h3>
                                <p>{{ Str::limit($post->plain_excerpt, 160) }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div style="margin-top:2rem;text-align:center;">{{ $posts->withQueryString()->links() }}</div>
            @else
                <p style="text-align:center;color:var(--secondary-color);">No posts found.</p>
            @endif
        </div>
    </section>
@endsection
