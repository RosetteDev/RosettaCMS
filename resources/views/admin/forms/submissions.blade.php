@extends('admin.layouts.app')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.forms-builder.index') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> All forms</a>
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">{{ $form->name }} — Submissions</h1>
                <p class="text-muted mb-0">Submissions received via this form.</p>
            </div>
            <a href="{{ route('form.show', $form->slug) }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-external-link-alt"></i> View public form</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="text-muted small">Total</div>
                    <div class="h5 mb-0">{{ $statistics['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="text-muted small">New</div>
                    <div class="h5 mb-0 text-success">{{ $statistics['new'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="text-muted small">Spam</div>
                    <div class="h5 mb-0 text-danger">{{ $statistics['spam'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-2">
                    <div class="text-muted small">Handled</div>
                    <div class="h5 mb-0 text-primary">{{ $statistics['handled'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="d-flex gap-2 flex-wrap">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search submissions…" class="form-control" style="max-width: 320px;">
                <select name="status" class="form-select" style="max-width: 180px;">
                    @foreach(['all', 'new', 'read', 'spam', 'handled'] as $value)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $value === 'all' ? 'All statuses' : ucfirst($value) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($submissions->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-inbox" style="font-size: 2.5rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                    No submissions yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Submitted</th>
                                <th>Data</th>
                                <th>Spam</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submissions as $submission)
                                <tr>
                                    <td style="white-space: nowrap;">
                                        <div>{{ $submission->created_at->format('Y-m-d H:i') }}</div>
                                        <div class="text-muted small">{{ $submission->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td style="max-width: 380px;">
                                        <div style="max-height: 3em; overflow: hidden;">
                                            @php
                                                $preview = collect($submission->data ?? [])->map(fn ($v, $k) => $k . ': ' . (is_scalar($v) ? $v : json_encode($v)))->take(3)->implode(' · ');
                                            @endphp
                                            {{ $preview ?: '(empty)' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ $submission->spam_score }}/100</span>
                                        @if($submission->spam_reason)
                                            <div class="text-muted small">{{ Illuminate\Support\Str::limit($submission->spam_reason, 50) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission->status === 'new')
                                            <span class="badge bg-success">New</span>
                                        @elseif($submission->status === 'read')
                                            <span class="badge bg-info text-dark">Read</span>
                                        @elseif($submission->status === 'spam')
                                            <span class="badge bg-danger">Spam</span>
                                        @else
                                            <span class="badge bg-secondary">Handled</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.forms-builder.submissions.show', [$form, $submission]) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 d-flex justify-content-center">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
