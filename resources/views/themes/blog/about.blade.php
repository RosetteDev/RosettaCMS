@extends('themes.blog.layout')

@section('content')
    <section class="container" style="padding:3.5rem 0;max-width:700px;">
        <h1 style="font-size:2.25rem;margin-bottom:1.5rem;">{{ $page->title ?? 'About' }}</h1>
        <div class="post-content">{!! $page->rendered_content ?? $page->content ?? 'Welcome to our story.' !!}</div>
    </section>
@endsection
