@extends('admin.layouts.app')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
        <div>
            <h1 style="margin-bottom: 0.25rem;">Submission #{{ $submission->id }}</h1>
            <p style="margin: 0; color: var(--text-muted);">Received {{ $submission->created_at->format('M j, Y g:i A') }}</p>
        </div>
        <a href="{{ url('/admin/forms') }}" class="btn btn-secondary">← Back to inbox</a>
    </div>

    @if(session('status'))
        <div style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">{{ session('status') }}</div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start;">
        <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Name</div>
                    <div style="font-weight: 600; color: var(--text-color);">{{ $submission->name }}</div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Email</div>
                    <div style="font-weight: 600; color: var(--text-color);"><a href="mailto:{{ $submission->email }}" style="color: var(--primary-color);">{{ $submission->email }}</a></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Subject</div>
                    <div style="font-weight: 600; color: var(--text-color);">{{ $submission->subject ?: '(none)' }}</div>
                </div>
            </div>

            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--text-color);">Message</h3>
            <div style="background: var(--background-color, #fff); border: 1px solid var(--border-color); border-radius: 6px; padding: 1rem; white-space: pre-wrap; color: var(--text-color); line-height: 1.6;">{{ $submission->message }}</div>

            @if($submission->custom_fields && count($submission->custom_fields) > 0)
                <h3 style="font-size: 1rem; margin: 1.5rem 0 0.5rem; color: var(--text-color);">Custom fields</h3>
                <div style="display: grid; gap: 0.5rem;">
                    @foreach($submission->custom_fields as $key => $value)
                        <div style="display: flex; justify-content: space-between; gap: 1rem; padding: 0.5rem 0.75rem; background: var(--background-color, #fff); border: 1px solid var(--border-color); border-radius: 6px;">
                            <span style="color: var(--text-muted); font-size: 0.85rem; text-transform: capitalize;">{{ str_replace('_', ' ', $key) }}</span>
                            <span style="color: var(--text-color); text-align: right;">{{ is_scalar($value) ? $value : json_encode($value) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <aside style="display: grid; gap: 1rem;">
            <div style="background-color: var(--light-color); border-radius: 8px; padding: 1rem;">
                <h3 style="font-size: 0.9rem; margin-bottom: 0.75rem; color: var(--text-color); text-transform: uppercase; letter-spacing: 0.05em;">Status</h3>
                <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.85rem;
                    @if($submission->status === 'new') background-color: #d1fae5; color: #065f46;
                    @elseif($submission->status === 'read') background-color: #e0e7ff; color: #3730a3;
                    @elseif($submission->status === 'spam') background-color: #fee2e2; color: #991b1b;
                    @else background-color: #f3e8ff; color: #6b21a8; @endif">
                    {{ ucfirst($submission->status) }}
                </span>
                <form method="POST" action="{{ url('/admin/forms/' . $submission->id . '/status') }}" style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    @csrf
                    <select name="status" class="form-select form-select-sm" style="width: auto; flex: 1;">
                        <option value="new" {{ $submission->status === 'new' ? 'selected' : '' }}>New</option>
                        <option value="read" {{ $submission->status === 'read' ? 'selected' : '' }}>Read</option>
                        <option value="spam" {{ $submission->status === 'spam' ? 'selected' : '' }}>Spam</option>
                        <option value="handled" {{ $submission->status === 'handled' ? 'selected' : '' }}>Handled</option>
                    </select>
                    <button type="submit" class="btn btn-primary" style="padding: 0.35rem 0.85rem;">Update</button>
                </form>
            </div>

            <div style="background-color: var(--light-color); border-radius: 8px; padding: 1rem;">
                <h3 style="font-size: 0.9rem; margin-bottom: 0.75rem; color: var(--text-color); text-transform: uppercase; letter-spacing: 0.05em;">Spam analysis</h3>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 0.5rem;">
                    <span style="color: var(--text-muted);">Score</span>
                    <strong style="color: {{ $submission->spam_score >= 50 ? '#ef4444' : '#10b981' }};">{{ $submission->spam_score }}/100</strong>
                </div>
                @if($submission->spam_reason)
                    <div style="font-size: 0.85rem; color: var(--text-color);">{{ $submission->spam_reason }}</div>
                @else
                    <div style="font-size: 0.85rem; color: var(--text-muted);">No signals detected.</div>
                @endif
            </div>

            <div style="background-color: var(--light-color); border-radius: 8px; padding: 1rem;">
                <h3 style="font-size: 0.9rem; margin-bottom: 0.75rem; color: var(--text-color); text-transform: uppercase; letter-spacing: 0.05em;">Metadata</h3>
                <div style="font-size: 0.85rem; color: var(--text-color); line-height: 1.7;">
                    <div><span style="color: var(--text-muted);">IP hash</span><br><code style="font-size: 0.75rem;">{{ $submission->ip_hash ?: '—' }}</code></div>
                    <div style="margin-top: 0.5rem;"><span style="color: var(--text-muted);">User agent</span><br>{{ $submission->user_agent ?: '—' }}</div>
                </div>
            </div>

            <form method="POST" action="{{ url('/admin/forms/' . $submission->id) }}" onsubmit="return confirm('Delete this submission? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="width: 100%;">Delete submission</button>
            </form>
        </aside>
    </div>
@endsection
