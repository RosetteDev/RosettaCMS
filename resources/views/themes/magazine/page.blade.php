@extends('themes.magazine.layout')

@section('content')
    <section class="container" style="padding:3.5rem 0;max-width:760px;">
        <h1 style="font-size:2.5rem;margin-bottom:1.5rem;">{{ $page->title ?? 'Page' }}</h1>
        <div class="post-content">{!! $page->rendered_content ?? $page->content !!}</div>
    </section>
@endsection
