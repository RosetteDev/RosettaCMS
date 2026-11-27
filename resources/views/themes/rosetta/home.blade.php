@extends('themes.rosetta.layout')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Welcome to {{ $settings->resolved_site_title ?? ($site->name ?? 'Rosetta CMS') }}</h1>
            <p style="font-size: 1.2rem; margin-top: 1rem;">
                {{ $settings->resolved_site_description ?? ($site->description ?? 'A modern, flexible content management system') }}
            </p>
            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ url('/blog') }}" class="btn btn-primary">Read the Blog</a>
                <a href="{{ url('/about') }}" class="btn btn-outline" style="border-color:#fff;color:#fff;">About Us</a>
            </div>
        </div>
    </div>

    @if($posts->isNotEmpty())
        <section style="padding: 3rem 0;">
            <div class="container">
                <div class="section-title">
                    <h2>Latest Posts</h2>
                    <p>Fresh content from across the site</p>
                </div>

                <div class="posts-grid">
                    @foreach($posts as $post)
                        <article class="post-item card">
                            @if($post->featured_image_url)
                                <img loading="lazy" decoding="async" src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="card-img">
                            @endif
                            <div class="card-content">
                                <div class="post-meta">
                                    <span class="post-date">{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</span>
                                    @if($post->primary_category)
                                        <span class="post-category">{{ $post->primary_category->name }}</span>
                                    @endif
                                </div>
                                <h3 class="post-title">
                                    <a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a>
                                </h3>
                                <p class="post-excerpt">
                                    {{ Str::limit($post->plain_excerpt, 160) }}
                                </p>
                                <a href="{{ url('/posts/' . $post->slug) }}" class="btn btn-primary">Read More</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div style="text-align:center;">
                    <a href="{{ url('/blog') }}" class="btn btn-outline">View all posts</a>
                </div>
            </div>
        </section>
    @endif

    <section style="padding: 3rem 0;">
        <div class="container">
            <div class="grid">
                <div class="card">
                    <div class="feature-icon">✍️</div>
                    <h3>Powerful Editor</h3>
                    <p>A rich, WordPress-style editor with SEO controls, excerpts, and featured images.</p>
                </div>
                <div class="card">
                    <div class="feature-icon">🗂️</div>
                    <h3>Organized Content</h3>
                    <p>Categories, tags, pages, and posts keep your content structured and easy to find.</p>
                </div>
                <div class="card">
                    <div class="feature-icon">🎨</div>
                    <h3>Replaceable Themes</h3>
                    <p>Switch between themes and customize colors, typography, and layout from Settings.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
