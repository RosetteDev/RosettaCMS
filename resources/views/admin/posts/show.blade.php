@extends('admin.layouts.app')

@section('title', $post->title)

@push('styles')
<style>
    @media (max-width: 640px) {
        .dl-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')
    <div class="hero">
        <div class="container">
            <h1>{{ $post->title }}</h1>
            <p>
                {{ ucfirst($post->post_type) }} &middot; {{ ucfirst($post->status) }}
                @if($post->published_at)
                    &middot; {{ $post->published_at->format('M j, Y g:i a') }}
                @endif
            </p>
        </div>
    </div>

    <div class="container" style="max-width: 900px; padding: 2rem;">
        @if(session('success'))
            <div style="padding: 0.75rem 1rem; border-radius: 6px; background-color: #d1fae5; color: #065f46; margin-bottom: 1.5rem;">
                {{ session('success') }}
            </div>
        @endif

        <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                <a href="{{ route($post->post_type === 'page' ? 'admin.pages.edit' : 'admin.posts.edit', $post) }}" class="btn btn-primary">Edit</a>
                @if($post->status === 'published')
                    <a href="{{ $post->public_url }}" target="_blank" rel="noopener" class="btn btn-outline">View on site</a>
                @endif
                <a href="{{ url($post->post_type === 'page' ? '/admin/pages' : '/admin/posts') }}" class="btn btn-outline">Back to list</a>
            </div>

            <dl class="dl-grid" style="display: grid; grid-template-columns: 160px 1fr; gap: 0.75rem 1rem; margin-bottom: 2rem; color: var(--text-color);">
                <dt style="font-weight: 600;">Slug</dt>
                <dd><code>{{ $post->slug }}</code></dd>

                <dt style="font-weight: 600;">Author</dt>
                <dd>{{ $post->user->name ?? 'Unknown' }}</dd>

                <dt style="font-weight: 600;">Comments</dt>
                <dd>{{ ucfirst($post->comment_status) }}</dd>

                @if($post->featured_image)
                    <dt style="font-weight: 600;">Featured image</dt>
                    <dd><img src="{{ $post->featured_image_url }}" alt="" style="max-width: 240px; border-radius: 6px;"></dd>
                @endif
            </dl>

            @if($post->excerpt)
                <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-color); margin-bottom: 0.5rem;">Excerpt</h2>
                <p style="color: var(--text-color); margin-bottom: 2rem;">{{ $post->excerpt }}</p>
            @endif

            <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-color); margin-bottom: 0.5rem;">Content</h2>
            <div style="color: var(--text-color); line-height: 1.7;">
                {!! $post->rendered_content ?: '<em>No content yet.</em>' !!}
            </div>
        </div>
    </div>
@endsection
