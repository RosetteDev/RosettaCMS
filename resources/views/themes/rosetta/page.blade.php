@extends('themes.rosetta.layout')

@section('content')
    @if(($inlineEdit ?? false) && $page && $page->exists && Auth::check() && Auth::user()->hasRole('admin'))
        <!-- Inline Edit Button -->
        <div class="rosetta-inline-edit float-end" style="position:fixed;top:2rem;right:1rem;z-index:1000;">
            <a href="{{ url('/pages/' . $page->slug . '?inline_edit=1') }}" class="btn btn-primary btn-lg rounded-circle shadow-lg" title="Edit this page">
                <i class="fas fa-pen-nib"></i>
            </a>
        </div>
        
        @include('themes.rosetta.partials.inline-edit-modal')
    @endif

    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>{{ $page->title ?? 'Page' }}</h1>
            @if($page?->excerpt)
                <p style="font-size: 1.15rem; margin-top: .75rem;">{{ $page->excerpt }}</p>
            @endif
        </div>
    </div>

    <!-- Page Content -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
                <div class="page-content post-content">
                    @php
                        $content = $page?->rendered_content ?? '';

                        if (function_exists('apply_filters')) {
                            $content = apply_filters('the_content', $content);
                        }

                        // Convert YouTube URLs to embedded iframes (handles watch, shorts, and embed URLs)
                        echo preg_replace_callback(
                            '/https?:\/\/(?:www\.)?youtube\.com\/(?:watch\?v=|shorts\/|embed\/)([a-zA-Z0-9_-]{11})/',
                            function ($matches) {
                                $videoId = $matches[1];
                                return '<div class="youtube-embed" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;"><iframe src="https://www.youtube.com/embed/' . $videoId . '" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
                            },
                            $content
                        );
                    @endphp
                </div>
            </div>
        </div>
    </section>

    @if($page && $page->featured_image_url)
        <section style="padding-bottom: 3rem;">
            <div class="container" style="max-width: 800px; margin: 0 auto;">
                <img src="{{ $page->featured_image_url }}" alt="{{ $page->title }}" style="width:100%;border-radius:.5rem;">
            </div>
        </section>
    @endif

    @if(($inlineEdit ?? false) && $page && $page->exists && Auth::check() && Auth::user()->hasRole('admin'))
        @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Open inline edit mode
            document.addEventListener('click', function(e) {
                if (e.target.closest('.rosetta-inline-edit')) {
                    e.preventDefault();
                    e.stopPropagation();
                    openInlineEdit();
                }
            });

            window.openInlineEdit = function() {
                const modal = document.getElementById('inlineEditModal');
                if (modal) {
                    modal.classList.add('show');
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                }
            };

            window.closeInlineEdit = function() {
                const modal = document.getElementById('inlineEditModal');
                if (modal) {
                    modal.classList.remove('show');
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }
            };
        });
        </script>
        @endpush
    @endif
@endsection
