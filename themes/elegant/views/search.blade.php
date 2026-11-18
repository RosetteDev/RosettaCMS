@extends('themes.elegant.layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="search-results-header mb-5 text-center">
                <h1 class="search-title fs-1 fw-light">Search Results for "{{ $query }}"</h1>
                @if($posts->count() > 0)
                    <p class="search-count fs-4 text-muted">Found {{ $posts->count() }} result{{ $posts->count() > 1 ? 's' : '' }}</p>
                @else
                    <p class="search-count fs-4 text-muted">No results found</p>
                @endif
            </div>

            @if($posts->count())
                <div class="row g-5">
                    @foreach($posts as $post)
                        <div class="col-md-6">
                            <article class="post-card h-100">
                                <div class="post-card-content">
                                    <h2 class="post-title h4">
                                        <a href="{{ route('post.show', $post->slug) }}" class="post-link text-decoration-none">
                                            {{ $post->title }}
                                        </a>
                                    </h2>
                                    <div class="post-meta mb-3">
                                        <span class="post-date">{{ $post->created_at->format('F j, Y') }}</span>
                                        @if($post->category)
                                            <span class="post-category ms-2">{{ $post->category->name }}</span>
                                        @endif
                                    </div>
                                    <p class="post-excerpt">
                                        {{ Str::limit($post->content, 150) }}
                                    </p>
                                    <a href="{{ route('post.show', $post->slug) }}" class="btn btn-outline-primary mt-2">
                                        Read More
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                <div class="pagination justify-content-center mt-5">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="no-posts text-center py-5">
                    <p class="text-muted fs-4">We couldn't find any results for your search. Try using different keywords.</p>
                    <a href="{{ url('/') }}" class="btn btn-outline-primary mt-3">
                        Go Back Home
                    </a>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            @include('themes.elegant.partials.sidebar')
        </div>
    </div>
</div>
@endsection
