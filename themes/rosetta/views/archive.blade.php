@extends('themes.rosetta.layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="archive-header mb-4">
                <h1 class="archive-title">@yield('archive-title', 'Archive')</h1>
                @yield('archive-description')
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
                    <p>No posts found in this archive.</p>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            @include('themes.rosetta.partials.sidebar')
        </div>
    </div>
</div>
@endsection
