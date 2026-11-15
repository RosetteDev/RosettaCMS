@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>{{ $page->title ?? 'Page' }}</h1>
        </div>
    </div>

    <!-- Page Content -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
                <div class="page-content">
                    {{ $page->content ?? '' }}
                </div>
            </div>
        </div>
    </section>
@endsection
