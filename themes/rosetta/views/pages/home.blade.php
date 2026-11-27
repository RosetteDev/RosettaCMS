@extends('themes.rosetta::layouts.app')

@section('content')
<!-- Hero Section -->
<div class="hero">
    <div class="container">
        <h1>Welcome to {{ $site->name ?? 'Rosetta CMS' }}</h1>
        <p style="font-size: 1.2rem; margin-top: 1rem;">A modern, flexible content management system built with Laravel</p>
        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('admin.login') }}" class="btn btn-primary">Get Started</a>
            <a href="https://github.com/RosettaDev/RosettaCMS" class="btn" style="background-color: white; color: #1f2937; border: 1px solid #e5e7eb;">View on GitHub</a>
        </div>
    </div>
</div>

<!-- Posts Section -->
@if($posts->count() > 0)
<section style="padding: 4rem 0;">
    <div class="container">
        <div class="section-title" style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; color: var(--text-color);">Latest Posts</h2>
            <p style="color: var(--secondary-color);">Stay up to date with our latest content</p>
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
    </div>
</section>
@endif

<!-- CTA Section -->
<section style="background: linear-gradient(135deg, var(--primary-color), #2563eb); color: white; padding: 4rem 0; text-align: center;">
    <div class="container">
        <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Get Started?</h2>
        <p style="font-size: 1.2rem; margin-bottom: 2rem;">Join thousands of developers building with Rosetta CMS</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="https://github.com/RosettaDev/RosettaCMS" class="btn" style="background-color: white; color: var(--primary-color);">Star on GitHub</a>
            <a href="https://github.com/RosettaDev/RosettaCMS/issues" class="btn" style="background-color: transparent; border: 1px solid white; color: white;">Report an Issue</a>
        </div>
    </div>
</section>
@endsection