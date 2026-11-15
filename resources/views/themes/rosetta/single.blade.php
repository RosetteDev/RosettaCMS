@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>{{ $post->title ?? 'Post' }}</h1>
            <p style="font-size: 1.2rem; margin-top: 0.5rem;">
                By {{ $post->author->name ?? 'Author' }} on {{ $post->published_at?->format('F j, Y') ?? now()->format('F j, Y') }}
            </p>
        </div>
    </div>

    <!-- Post Content -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
                @if($post->featured_image)
                    <img src="{{ $post->featured_image->url ?? '' }}" alt="{{ $post->title }}" style="width: 100%; border-radius: 8px; margin-bottom: 2rem;">
                @endif
                
                <div class="post-content">
                    {{ $post->content ?? '' }}
                </div>
                
                <!-- Categories and Tags -->
                @if($post->categories()->count() > 0 || $post->tags()->count() > 0)
                    <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                        @if($post->categories()->count() > 0)
                            <div style="margin-bottom: 1rem;">
                                <strong>Categories:</strong>
                                @foreach($post->categories as $category)
                                    <a href="{{ url('/categories/' . $category->slug) }}" style="display: inline-block; margin-right: 0.5rem; padding: 0.25rem 0.75rem; background-color: var(--light-color); border-radius: 4px; text-decoration: none; color: var(--text-color);">{{ $category->name }}</a>
                                @endforeach
                            </div>
                        @endif
                        
                        @if($post->tags()->count() > 0)
                            <div>
                                <strong>Tags:</strong>
                                @foreach($post->tags as $tag)
                                    <a href="{{ url('/tags/' . $tag->slug) }}" style="display: inline-block; margin-right: 0.5rem; padding: 0.25rem 0.75rem; background-color: var(--light-color); border-radius: 4px; text-decoration: none; color: var(--text-color);">{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
