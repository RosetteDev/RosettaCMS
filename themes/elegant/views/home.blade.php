@extends('themes.elegant::layouts.app')

@section('content')
<!-- Hero Section -->
<div class="hero">
    <div class="container">
        <h1>Welcome to {{ $settings->resolved_site_title ?? ($site->name ?? 'Elegant CMS') }}</h1>
        <p style="font-size: 1.2rem; margin-top: 1rem;">{{ $settings->resolved_site_description ?? ($site->description ?? 'A modern, flexible content management system built with Laravel') }}</p>
        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ url('/blog') }}" class="btn">Read the Blog</a>
            <a href="{{ url('/about') }}" class="btn btn-secondary">About Us</a>
        </div>
    </div>
</div>

<!-- Posts Section -->
@if($posts->isNotEmpty())
<section style="padding: 4rem 0;">
    <div class="container">
        <div class="text-center mb-4" style="margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; color: var(--text-color);">Latest Posts</h2>
            <p style="color: var(--secondary-color);">Fresh content from across the site</p>
        </div>

        <div class="posts-grid">
            @foreach($posts->take(6) as $post)
            <div class="card post-item">
                @if($post->featured_image)
                <img src="{{ $post->featured_image->url ?? '' }}" alt="{{ $post->title }}" class="card-img">
                @endif
                <div class="card-content">
                    <div class="post-meta">
                        <span>{{ $post->published_at?->format('F j, Y') ?? now()->format('F j, Y') }}</span>
                    </div>
                    <h3 style="margin-bottom: 0.75rem;">
                        <a href="{{ url('/posts/' . $post->slug) }}" style="color: var(--text-color); text-decoration: none;">{{ $post->title }}</a>
                    </h3>
                    <p style="color: var(--secondary-color); margin-bottom: 1rem;">{{ Str::limit($post->excerpt ?? strip_tags($post->content), 150) }}</p>
                    <a href="{{ url('/posts/' . $post->slug) }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">Read More →</a>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ url('/blog') }}" class="btn">View All Posts</a>
        </div>
    </div>
</section>
@endif
@endsection
