@extends('themes.rosetta.layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="search-results-header mb-4">
                <h1 class="search-title">Search Results for "{{ $query }}"</h1>
                @if($posts->count() > 0)
                    <p class="search-count">Found {{ $posts->count() }} result{{ $posts->count() > 1 ? 's' : '' }}</p>
                @else
                    <p class="search-count">No results found</p>
                @endif
            </div>

            @if($posts->count())
                @foreach($posts as $post)
                    <article class="post-card mb-4">
                        <div class="post-card-content">
                            <h2 class="post-title">
                                <a href="{{ route('post.show', $post->slug) }}" class="post-link">
                                    {{ $post->title }}
                                </a>
                            </h2>
                            <div class="post-meta mb-2">
                                <span class="post-date">{{ $post->created_at->format('F j, Y') }}</span>
                                @if($post->category)
                                    <span class="post-category">in {{ $post->category->name }}</span>
                                @endif
                            </div>
                            <div class="post-excerpt">
                                {{ Str::limit($post->content, 200) }}
                            </div>
                            <a href="{{ route('post.show', $post->slug) }}" class="btn btn-primary mt-2">
                                Read More
                            </a>
                        </div>
                    </article>
                @endforeach

                <div class="pagination">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="no-posts">
                    <p>We couldn't find any results for your search. Try using different keywords.</p>
                    <a href="{{ url('/') }}" class="btn btn-outline-primary mt-2">
                        Go Back Home
                    </a>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            @include('themes.rosetta.partials.sidebar')
        </div>
    </div>
</div>
@endsection
