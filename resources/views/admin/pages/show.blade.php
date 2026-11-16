@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $page->title }}</h1>
            <p>Page Details</p>
        </div>
    </div>

    <div class="container">
        <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.5rem;">
            <div style="margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">{{ $page->title }}</h2>
                <p style="color: var(--text-color); font-size: 0.9rem;">
                    <strong>Slug:</strong> {{ $page->slug }}
                </p>
                <p style="color: var(--text-color); font-size: 0.9rem;">
                    <strong>Status:</strong> 
                    <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem;
                        @if($page->status === 'published') background-color: var(--success-color); color: white;
                        @elseif($page->status === 'draft') background-color: var(--warning-color); color: white;
                        @else background-color: var(--secondary-color); color: white; @endif">
                        {{ ucfirst($page->status) }}
                    </span>
                </p>
                <p style="color: var(--text-color); font-size: 0.9rem;">
                    <strong>Author:</strong> {{ $page->user->name ?? 'Unknown' }}
                </p>
                <p style="color: var(--text-color); font-size: 0.9rem;">
                    <strong>Published:</strong> {{ $page->published_at ? $page->published_at->format('M j, Y g:i A') : 'Not published' }}
                </p>
            </div>

            <div style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                <h3>Content</h3>
                <div style="margin-top: 1rem;">
                    {!! $page->content !!}
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <a href="{{ url('/admin/pages/' . $page->id . '/edit') }}" class="btn btn-primary">Edit Page</a>
                <a href="{{ url('/admin/pages') }}" class="btn btn-outline">Back to Pages</a>
            </div>
        </div>
    </div>
@endsection
