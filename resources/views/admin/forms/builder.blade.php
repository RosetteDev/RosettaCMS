@extends('admin.layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Forms</h1>
            <p class="text-muted mb-0">Contact Form 7-style form builder. Create forms, embed them on any page, and review submissions.</p>
        </div>
        <a href="{{ route('admin.forms-builder.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New form</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total forms</div>
                    <div class="h3 mb-0">{{ $stats['total_forms'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Enabled</div>
                    <div class="h3 mb-0 text-success">{{ $stats['enabled_forms'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Submissions</div>
                    <div class="h3 mb-0">{{ $stats['total_submissions'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">New submissions</div>
                    <div class="h3 mb-0 text-primary">{{ $stats['new_submissions'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($forms->isEmpty())
                <div class="p-5 text-center">
                    <i class="fas fa-file-alt" style="font-size: 2.5rem; color: var(--text-muted, #6b7280); opacity: 0.5;"></i>
                    <p class="text-muted mt-3 mb-3">No forms yet. Create your first form to start collecting submissions.</p>
                    <a href="{{ route('admin.forms-builder.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> New form</a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Fields</th>
                                <th>Submissions</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($forms as $form)
                                <tr>
                                    <td>
                                        <strong>{{ $form->name }}</strong>
                                        @if($form->description)
                                            <div class="text-muted small">{{ Illuminate\Support\Str::limit($form->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td><code>{{ $form->slug }}</code></td>
                                    <td>{{ count($form->fields ?? []) }}</td>
                                    <td>
                                        <a href="{{ route('admin.forms-builder.submissions.index', $form) }}">{{ $form->submissions_count }}</a>
                                    </td>
                                    <td>
                                        @if($form->enabled)
                                            <span class="badge bg-success">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('form.show', $form->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View public form"><i class="fas fa-external-link-alt"></i></a>
                                        <a href="{{ route('admin.forms-builder.submissions.index', $form) }}" class="btn btn-sm btn-outline-secondary" title="View submissions"><i class="fas fa-inbox"></i></a>
                                        <a href="{{ route('admin.forms-builder.edit', $form) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <form method="POST" action="{{ route('admin.forms-builder.destroy', $form) }}" style="display:inline" onsubmit="return confirm('Delete this form and its submissions? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
