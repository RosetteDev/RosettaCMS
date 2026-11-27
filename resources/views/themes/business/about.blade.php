@extends('themes.business.layout')

@section('content')
    <section class="section" style="padding-top:3rem;">
        <div class="container" style="max-width:820px;">
            <h1 style="font-size:2.5rem;margin-bottom:1.5rem;">{{ $page->title ?? 'About Us' }}</h1>
            <div class="post-content">{!! $page->rendered_content ?? $page->content ?? 'Welcome to our story.' !!}</div>
        </div>
    </section>
@endsection
