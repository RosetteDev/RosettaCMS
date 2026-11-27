@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Activity Log</h1>
            <p>Audit trail of admin actions: who changed what, and when.</p>
        </div>
    </div>

    <div style="padding: 1rem 0;">
        <form method="GET" action="{{ route('admin.activity.index') }}" style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: end;">
            <div>
                <label for="user_id" style="display:block; font-size: 0.8rem; font-weight: 500; margin-bottom: 0.25rem;">User</label>
                <select id="user_id" name="user_id" class="form-control form-control-sm" style="min-width: 180px;">
                    <option value="">All users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="action" style="display:block; font-size: 0.8rem; font-weight: 500; margin-bottom: 0.25rem;">Action filter</label>
                <input type="text" id="action" name="action" class="form-control form-control-sm" value="{{ request('action') }}" placeholder="e.g. categories.store" style="min-width: 180px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.5rem 1rem;">Filter</button>
            <a href="{{ route('admin.activity.index') }}" class="btn btn-outline-secondary btn-sm" style="padding: 0.5rem 1rem;">Clear</a>
        </form>
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if($logs->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-history fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No activity recorded yet.</p>
            </div>
        @else
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td style="white-space: nowrap;">{{ $log->created_at?->diffForHumans() }}</td>
                            <td>{{ $log->user?->name ?? '—' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td>{{ $log->description ?: '—' }}</td>
                            <td><code>{{ $log->ip ?: '—' }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <div style="padding: 0.75rem 1rem; border-top: 1px solid var(--border-color);">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
