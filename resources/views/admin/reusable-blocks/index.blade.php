@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Reusable Blocks</h1>
            <p>Named templates of blocks you can drop into any page from the page builder.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-primary">
                <i class="fas fa-pen-nib me-1"></i> Open Page Builder
            </a>
        </div>
        <div class="text-muted small">
            Save a block as reusable from the page builder's toolbar.
        </div>
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if($reusableBlocks->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-copy fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No reusable blocks yet. Select a block in the page builder and click <strong>Save as reusable</strong>.</p>
            </div>
        @else
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Blocks</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reusableBlocks as $reusableBlock)
                        <tr>
                            <td>
                                <strong>{{ $reusableBlock->name }}</strong>
                                @if($reusableBlock->description)
                                    <div class="text-muted small">{{ $reusableBlock->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $reusableBlock->blockCount() }}</span>
                                <span class="text-muted small">
                                    @foreach($reusableBlock->blocks() as $b)
                                        {{ $b['type'] }}@if(!$loop->last), @endif
                                    @endforeach
                                </span>
                            </td>
                            <td class="text-muted small">{{ $reusableBlock->created_at->diffForHumans() }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.reusable-blocks.destroy', $reusableBlock) }}" style="display:inline" onsubmit="return confirm('Delete this reusable block?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
