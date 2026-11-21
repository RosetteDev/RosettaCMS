@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Revisions for: {{ $post->title }}</h1>
            <p>View and restore previous versions of this post</p>
        </div>
    </div>

    <!-- Revisions List -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($revisions) > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-color); color: white;">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">Revision</th>
                        <th style="padding: 1rem; text-align: left;">Date</th>
                        <th style="padding: 1rem; text-align: left;">Author</th>
                        <th style="padding: 1rem; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($revisions as $revision)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: var(--secondary-color); color: white;">
                                    #{{ $revision->revision_number }}
                                </span>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $revision->revision_date->diffForHumans() }}
                                <div style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">
                                    {{ $revision->revision_date->format('M d, Y H:i') }}
                                </div>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $revision->user->name ?? 'Unknown' }}
                                <div style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">
                                    {{ $revision->revision_type }}
                                </div>
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <a href="{{ route('admin.posts.revisions.show', [$post->id, $revision->id]) }}" class="btn btn-primary" style="padding: 0.5rem 1rem; margin: 0.25rem;">View</a>
                                <a href="{{ route('admin.posts.revisions.restore', [$post->id, $revision->id]) }}" class="btn btn-success" style="padding: 0.5rem 1rem; margin: 0.25rem;" onclick="return confirm('Restore this revision? This will replace the current content.');">Restore</a>
                                <a href="{{ route('admin.posts.revisions.diff', $post->id) }}?from={{ $revision->id }}&to={{ $revision->id }}" class="btn btn-outline-secondary" style="padding: 0.5rem 1rem; margin: 0.25rem;">Diff</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div style="padding: 1rem; display: flex; justify-content: center;">
                {{ $revisions->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No revisions found for this post.</p>
            </div>
        @endif
    </div>

    <!-- Navigation -->
    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
        <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left me-1"></i>Back to Edit
        </a>
    </div>
@endsection
