@php
    $parsedContent = '';
    if ($page->content) {
        // Check if content looks like HTML (contains tags)
        if (preg_match('/<\/[a-z][^>]*>/i', $page->content)) {
            // Content is already HTML - use as-is
            $parsedContent = $page->content;
        } else {
            // Content is Markdown - convert to HTML
            $config = [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ];
            $converter = new \League\CommonMark\CommonMarkConverter($config);
            $parsedContent = $converter->convertToHtml($page->content);
        }
    }
@endphp

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
                    {!! $parsedContent !!}
                </div>
            </div>
        </div>
    </section>
@endsection
