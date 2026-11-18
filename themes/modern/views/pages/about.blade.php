@extends('themes.modern.views.layouts.app')

@section('content')
<div class="content">
    <div class="container">
        <h1 style="font-size: 2.5rem; margin-bottom: 2rem; color: var(--primary-color);">{{ $page->title ?? 'About' }}</h1>
        
        <div class="post-content" style="line-height: 1.8; font-size: 1.125rem;">
            {{ $page->content ?? 'Welcome to the about page.' }}
        </div>
    </div>
</div>
@endsection