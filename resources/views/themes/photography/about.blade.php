@extends('themes.photography.layout')

@section('content')
    <section class="section" style="padding-top:4rem;">
        <div class="container" style="max-width:760px;">
            <h1 style="font-size:2.75rem;margin-bottom:1.5rem;">{{ $page->title ?? 'About' }}</h1>
            <div class="post-content">{!! $page->rendered_content ?? $page->content ?? 'Welcome to our story.' !!}</div>
        </div>
    </section>
@endsection
