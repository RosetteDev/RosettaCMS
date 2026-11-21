@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Revision Details</h1>
            <p>Revision #{{ $revision->revision_number }} for: {{ $post->title }}</p>
        </div>
    </div>

    <div class="row">
        <!-- Revision Info -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Revision Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Revision Number:</strong>
                        <span class="badge bg-secondary ms-2">#{{ $revision->revision_number }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Revision Date:</strong>
                        <div>{{ $revision->revision_date->format('F d, Y H:i:s') }}</div>
                        <div class="text-muted small">{{ $revision->revision_date->diffForHumans() }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Author:</strong>
                        <div>{{ $revision->user->name ?? 'Unknown' }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Revision Type:</strong>
                        <span class="badge bg-info">{{ $revision->revision_type }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-secondary">{{ $revision->status }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.posts.revisions.restore', [$post->id, $revision->id]) }}" 
                       class="btn btn-success w-100 mb-2" 
                       onclick="return confirm('Restore this revision? This will replace the current content. Are you sure?');">
                        <i class="fas fa-undo me-2"></i>Restore This Revision
                    </a>
                    <a href="{{ route('admin.posts.revisions.index', $post->id) }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-2"></i>Back to Revisions
                    </a>
                    <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" class="btn btn-primary w-100">
                        <i class="fas fa-edit me-2"></i>Edit Current Post
                    </a>
                </div>
            </div>
        </div>

        <!-- Revision Content -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Content Preview</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Title</h6>
                        <div class="p-3 bg-light border rounded">{{ $revision->title }}</div>
                    </div>

                    <div class="mb-4">
                        <h6>Content</h6>
                        <div class="p-3 bg-light border rounded" style="max-height: 500px; overflow-y: auto;">
                            @if($revision->content)
                                {!! $revision->content !!}
                            @else
                                <p class="text-muted">No content saved in this revision.</p>
                            @endif
                        </div>
                    </div>

                    @if($revision->excerpt)
                    <div class="mb-4">
                        <h6>Excerpt</h6>
                        <div class="p-3 bg-light border rounded">{{ $revision->excerpt }}</div>
                    </div>
                    @endif

                    @if($revision->meta)
                    <div class="mb-4">
                        <h6>Meta Data</h6>
                        <div class="p-3 bg-light border rounded">
                            <pre class="small mb-0">{{ json_encode($revision->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
