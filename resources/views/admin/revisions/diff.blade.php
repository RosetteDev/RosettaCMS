@extends('admin.layouts.app')

@push('styles')
<style>
    .diff-container {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        line-height: 1.6;
        white-space: pre-wrap;
    }
    
    .diff-line {
        padding: 2px 10px;
        margin: 0;
    }
    
    .diff-unchanged {
        color: #666;
    }
    
    .diff-added {
        background-color: #d4edda;
        color: #155724;
    }
    
    .diff-deleted {
        background-color: #f8d7da;
        color: #721c24;
        text-decoration: line-through;
    }
    
    .diff-header {
        padding: 1rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .diff-content {
        padding: 1rem;
        max-height: 70vh;
        overflow-y: auto;
    }

    .diff-side {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        line-height: 1.6;
    }

    .diff-side-col {
        padding: 0.5rem 0.75rem;
        white-space: pre-wrap;
        overflow-wrap: break-word;
        max-height: 60vh;
        overflow-y: auto;
    }

    .diff-side-col:first-child {
        border-right: 1px solid #dee2e6;
    }

    .diff-side-head {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        font-family: inherit;
        font-weight: 600;
        font-size: 0.75rem;
        color: #555;
        padding: 0.4rem 0.75rem;
        border-bottom: 1px solid #dee2e6;
        z-index: 1;
    }

    .diff-row-removed { background: #f8d7da; color: #721c24; }
    .diff-row-added { background: #d4edda; color: #155724; }
    .diff-row-same { color: #666; }

    @media (max-width: 767px) {
        .diff-side { grid-template-columns: 1fr; }
        .diff-side-col:first-child { border-right: none; border-bottom: 1px solid #dee2e6; }
    }
    
    .diff-controls {
        padding: 1rem;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
</style>
@endpush

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Visual Diff</h1>
            <p>Compare revisions for: {{ $post->title }}</p>
        </div>
    </div>

    <!-- Revision Comparison Info -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Revision Comparison</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>From Revision #{{ $fromRevision->revision_number }}</h6>
                    <p class="mb-1">
                        <strong>Date:</strong> {{ $fromRevision->revision_date->format('F d, Y H:i:s') }} 
                        ({{ $fromRevision->revision_date->diffForHumans() }})
                    </p>
                    <p class="mb-1">
                        <strong>Author:</strong> {{ $fromRevision->user->name ?? 'Unknown' }}
                    </p>
                    <p class="mb-0">
                        <strong>Type:</strong> {{ $fromRevision->revision_type }}
                    </p>
                </div>
                <div class="col-md-6">
                    <h6>To Revision #{{ $toRevision->revision_number }}</h6>
                    <p class="mb-1">
                        <strong>Date:</strong> {{ $toRevision->revision_date->format('F d, Y H:i:s') }} 
                        ({{ $toRevision->revision_date->diffForHumans() }})
                    </p>
                    <p class="mb-1">
                        <strong>Author:</strong> {{ $toRevision->user->name ?? 'Unknown' }}
                    </p>
                    <p class="mb-0">
                        <strong>Type:</strong> {{ $toRevision->revision_type }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Diff Content -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Content Differences</h5>
        </div>
        <div class="card-body">
            <!-- Title Diff -->
            @if($fromRevision->title !== $toRevision->title)
            <div class="mb-4">
                <h6><i class="fas fa-heading"></i> Title</h6>
                <div class="diff-container">
                    <div class="diff-line diff-deleted">
                        <i class="fas fa-minus-circle"></i> {{ $fromRevision->title }}
                    </div>
                    <div class="diff-line diff-added">
                        <i class="fas fa-plus-circle"></i> {{ $toRevision->title }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Content Diff -->
            <div class="mb-4">
                <h6><i class="fas fa-file-alt"></i> Content <span class="text-muted small fw-normal">(side by side)</span></h6>
                @if(count($diff) > 0)
                    <div class="diff-side">
                        <div>
                            <div class="diff-side-head">From · #{{ $fromRevision->revision_number }}</div>
                            <div class="diff-side-col">
                                @foreach($diff as $line)
                                    @if($line['type'] === 'unchanged')
                                        <div class="diff-row-same">{{ e($line['from']) }}</div>
                                    @elseif($line['type'] === 'deleted')
                                        <div class="diff-row-removed">{{ e($line['from']) }}</div>
                                    @elseif($line['type'] === 'added')
                                        <div class="diff-row-same">&nbsp;</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <div class="diff-side-head">To · #{{ $toRevision->revision_number }}</div>
                            <div class="diff-side-col">
                                @foreach($diff as $line)
                                    @if($line['type'] === 'unchanged')
                                        <div class="diff-row-same">{{ e($line['to']) }}</div>
                                    @elseif($line['type'] === 'added')
                                        <div class="diff-row-added">{{ e($line['to']) }}</div>
                                    @elseif($line['type'] === 'deleted')
                                        <div class="diff-row-same">&nbsp;</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-muted">No differences found.</p>
                @endif
            </div>

            <!-- Excerpt Diff -->
            @if(($fromRevision->excerpt ?? '') !== ($toRevision->excerpt ?? ''))
            <div class="mb-4">
                <h6><i class="fas fa-quote-left"></i> Excerpt</h6>
                <div class="diff-container">
                    @if($fromRevision->excerpt)
                    <div class="diff-line diff-deleted">
                        <i class="fas fa-minus-circle"></i> {{ e($fromRevision->excerpt) }}
                    </div>
                    @endif
                    @if($toRevision->excerpt)
                    <div class="diff-line diff-added">
                        <i class="fas fa-plus-circle"></i> {{ e($toRevision->excerpt) }}
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between">
                <div>
                    <span class="badge bg-secondary me-2">Unchanged</span>
                    <span class="badge bg-success me-2">Added</span>
                    <span class="badge bg-danger">Deleted</span>
                </div>
                <div>
                    <a href="{{ route('admin.posts.revisions.index', $post->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Revisions
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
