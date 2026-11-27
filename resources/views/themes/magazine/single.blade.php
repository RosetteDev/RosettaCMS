@extends('themes.magazine.layout')

@section('content')
    <article>
        <header class="mag-single-hero">
            <div class="container">
                <div class="kicker">{{ $post->categories->first()->name ?? 'Story' }}</div>
                <h1>{{ $post->title }}</h1>
                <div class="byline">By {{ $post->author->name ?? 'Author' }} &middot; {{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</div>
            </div>
        </header>

        <div class="container">
            @if($post->featured_image_url)
                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
            @endif
            <div class="post-content" style="max-width:760px;margin:0 auto;padding-bottom:3rem;">{!! $post->rendered_content !!}</div>
        </div>
    </article>
@endsection
