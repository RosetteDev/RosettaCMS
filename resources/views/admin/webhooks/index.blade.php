@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Webhooks</h1>
            <p>Notify external systems when content changes — posts, pages, comments and media.</p>
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

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('webhook_secret'))
        <div class="container mt-3">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Signing secret (shown once):</strong>
                <code class="d-block my-2 p-2 bg-white rounded" style="user-select: all; word-break: break-all;">{{ session('webhook_secret') }}</code>
                <small>Use this to verify the <code>X-Rosetta-Signature</code> header (HMAC-SHA256 of the request body).</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ route('admin.webhooks.create') }}" class="btn btn-primary">+ New Webhook</a>
        </div>
        <div class="text-muted small">
            Scope: <strong>{{ $site?->name ?? 'All sites' }}</strong>
        </div>
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if($webhooks->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-plug fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No webhooks yet. Create one to start receiving content notifications.</p>
            </div>
        @else
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Events</th>
                        <th>Last delivery</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($webhooks as $webhook)
                        <tr>
                            <td>
                                <div style="max-width: 340px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <code class="small">{{ $webhook->url }}</code>
                                </div>
                                @if($webhook->description)
                                    <div class="small text-muted">{{ $webhook->description }}</div>
                                @endif
                                @if($webhook->site_id === null)
                                    <span class="badge bg-light text-dark border mt-1">All sites</span>
                                @endif
                            </td>
                            <td>
                                @foreach($webhook->events ?? [] as $event)
                                    <span class="badge bg-secondary me-1 mb-1">{{ $event }}</span>
                                @endforeach
                            </td>
                            <td class="small">
                                @if($webhook->last_fired_at)
                                    <div>{{ $webhook->last_fired_at->diffForHumans() }}</div>
                                    <div class="text-muted">
                                        @if($webhook->last_response_code)
                                            HTTP {{ $webhook->last_response_code }}
                                        @else
                                            Failed
                                        @endif
                                        @if($webhook->last_error)
                                            · <span style="color: #dc2626;">{{ $webhook->last_error }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                @if($webhook->active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.webhooks.test', $webhook) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Test</button>
                                </form>
                                <form method="POST" action="{{ route('admin.webhooks.destroy', $webhook) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this webhook? Notifications will stop.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="alert alert-info mt-4" style="font-size: .85rem;">
        <strong>How it works:</strong> when an event fires, each subscribed webhook receives a signed JSON POST.
        Verify the <code>X-Rosetta-Signature</code> header — HMAC-SHA256 of the raw body using your signing secret,
        compared as <code>sha256=&lt;hex&gt;</code>. Delivery is best-effort: failures are recorded above, never thrown.
    </div>
@endsection
