@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>API Keys</h1>
            <p>Manage the API keys you use to authenticate against the public API.</p>
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
            <a href="{{ route('admin.api-keys.create') }}" class="btn btn-primary">+ New API Key</a>
        </div>
        <div class="text-muted small">
            API base URL: <code>{{ url('/api/v1') }}</code>
        </div>
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if($keys->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-key fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No API keys yet. Create one to start using the public API.</p>
            </div>
        @else
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Scopes</th>
                        <th>Created</th>
                        <th>Last used</th>
                        <th>Expires</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($keys as $key)
                        <tr>
                            <td><strong>{{ $key->name }}</strong></td>
                            <td><code>{{ $key->key_prefix }}…</code></td>
                            <td>
                                @foreach($key->scopes ?? [] as $scope)
                                    <span class="badge bg-secondary me-1">{{ $scope }}</span>
                                @endforeach
                                @if(empty($key->scopes))
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $key->created_at->format('Y-m-d H:i') }}</td>
                            <td class="small text-muted">{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="small text-muted">{{ $key->expires_at?->format('Y-m-d') ?? 'Never' }}</td>
                            <td>
                                @if($key->revoked_at)
                                    <span class="badge bg-danger">Revoked</span>
                                @elseif($key->expires_at && $key->expires_at->isPast())
                                    <span class="badge bg-warning text-dark">Expired</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$key->revoked_at)
                                    <form method="POST" action="{{ route('admin.api-keys.destroy', $key) }}" class="d-inline"
                                          onsubmit="return confirm('Revoke this API key? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Revoke</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </div>
@endsection
