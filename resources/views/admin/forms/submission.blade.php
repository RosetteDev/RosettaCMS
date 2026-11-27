@extends('admin.layouts.app')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.forms-builder.submissions.index', $form) }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fas fa-arrow-left"></i> Submissions</a>
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Submission #{{ $submission->id }}</h1>
                <p class="text-muted mb-0">{{ $form->name }} · {{ $submission->created_at->format('Y-m-d H:i') }} · {{ $submission->created_at->diffForHumans() }}</p>
            </div>
            <div class="d-flex gap-2">
                @if($submission->status !== 'handled')
                    <form method="POST" action="{{ route('admin.forms-builder.submissions.status', [$form, $submission]) }}" style="display:inline">
                        @csrf
                        <input type="hidden" name="status" value="handled">
                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i> Mark handled</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.forms-builder.submissions.destroy', [$form, $submission]) }}" style="display:inline" onsubmit="return confirm('Delete this submission?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h2 class="h5 mb-0">Submission data</h2></div>
                <div class="card-body">
                    @php
                        $fields = $form->fields ?? [];
                        $data = $submission->data ?? [];
                    @endphp
                    @if(count($fields) > 0)
                        @foreach($fields as $field)
                            <div class="mb-3">
                                <div class="text-muted small text-uppercase" style="letter-spacing: 0.05em;">{{ $field['label'] }}</div>
                                <div class="mt-1" style="white-space: pre-wrap;">{{ $data[$field['name']] ?? '—' }}</div>
                            </div>
                        @endforeach
                    @else
                        <dl class="row mb-0">
                            @foreach($data as $key => $value)
                                <dt class="col-sm-4 text-muted small">{{ $key }}</dt>
                                <dd class="col-sm-8" style="white-space: pre-wrap;">{{ is_scalar($value) ? $value : json_encode($value) }}</dd>
                            @endforeach
                        </dl>
                    @endif
                    @if(count($data) === 0)
                        <p class="text-muted mb-0">This submission has no data.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent"><h2 class="h5 mb-0">Status</h2></div>
                <div class="card-body">
                    <div class="mb-3">
                        <form method="POST" action="{{ route('admin.forms-builder.submissions.status', [$form, $submission]) }}">
                            @csrf
                            <label class="form-label small">Change status</label>
                            <div class="d-flex gap-2">
                                <select name="status" class="form-select">
                                    @foreach(['new', 'read', 'spam', 'handled'] as $value)
                                        <option value="{{ $value }}" @selected($submission->status === $value)>{{ ucfirst($value) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-outline-primary">Save</button>
                            </div>
                        </form>
                    </div>
                    <div class="text-muted small">Current: <strong>{{ ucfirst($submission->status) }}</strong></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h2 class="h5 mb-0">Spam analysis</h2></div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-muted small">Score</span>
                        <div class="h4 mb-0">{{ $submission->spam_score }}/100</div>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small">Reason</span>
                        <div>{{ $submission->spam_reason ?: 'None' }}</div>
                    </div>
                    @if($submission->ip_hash)
                        <div class="mb-2">
                            <span class="text-muted small">IP hash</span>
                            <div class="text-muted small" style="word-break: break-all;">{{ $submission->ip_hash }}</div>
                        </div>
                    @endif
                    @if($submission->user_agent)
                        <div>
                            <span class="text-muted small">User agent</span>
                            <div class="text-muted small" style="word-break: break-word;">{{ $submission->user_agent }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
