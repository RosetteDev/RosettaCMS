@extends('themes.rosetta.layout')

@section('content')
    <!-- Category Header -->
    <div class="hero">
        <div class="container">
            <h1>Category: {{ $category->name }}</h1>
            <p>{{ $category->description ?? 'Browse all posts in this category.' }}</p>
        </div>
    </div>

    <!-- Category Posts -->
    <section style="padding: 2rem 0;">
        <div class="container">
            @if(count($posts) > 0)
                <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">
                    @foreach($posts as $post)
                        <div class="card" style="background-color: var(--light-color); border-radius: 8px; overflow: hidden; transition: all 0.3s;">
                            @if($post->featured_image)
                                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" style="width: 100%; height: 200px; object-fit: cover;">
                            @endif
                            <div style="padding: 1.5rem;">
                                <div style="margin-bottom: 0.5rem;">
                                    @foreach($post->terms as $term)
                                        <span style="display: inline-block; background-color: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; margin-right: 0.25rem;">
                                            {{ $term->name }}
                                        </span>
                                    @endforeach
                                </div>
                                <h2 style="margin-bottom: 0.75rem;">
                                    <a href="{{ url('/posts/' . $post->slug) }}" style="color: var(--primary-color); text-decoration: none;">
                                        {{ $post->title }}
                                    </a>
                                </h2>
                                <p style="color: var(--text-color); margin-bottom: 1rem;">
                                    {{ Str::limit($post->content, 200) }}
                                </p>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--text-color);">
                                    <span>By {{ $post->author->name ?? 'Author' }}</span>
                                    <span>{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div style="margin-top: 2rem; text-align: center;">
                    {{ $posts->links() }}
                </div>
            @else
                <p style="text-align: center; color: var(--text-color);">No posts found in this category.</p>
            @endif
        </div>
    </section>
@endsection
