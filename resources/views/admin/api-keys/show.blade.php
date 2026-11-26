@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>API Key Created</h1>
            <p>Copy the key below — it will not be shown again.</p>
        </div>
    </div>

    <div class="container">
        <div class="col-lg-8 mx-auto">
            <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
                @if($plaintext)
                    <div class="alert alert-warning">
                        <i class="fas fa-triangle-exclamation me-2"></i>
                        <strong>This is the only time the full key will be displayed.</strong> Store it somewhere safe.
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Full key</label>
                        <div class="d-flex gap-2">
                            <input type="text" id="fullKey" value="{{ $plaintext }}" readonly
                                   style="flex: 1; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-family: monospace; font-size: 0.85rem; background-color: var(--bg-color);">
                            <button type="button" class="btn btn-primary" onclick="copyKey()">Copy</button>
                        </div>
                    </div>
                    <h3 class="h6">Example requests</h3>
                    <pre style="background-color: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 6px; overflow-x: auto; font-size: 0.8rem;"><code>curl -H "Authorization: Bearer {{ $plaintext }}" \\
  {{ url('/api/v1/posts') }}

curl -H "Authorization: Bearer {{ $plaintext }}" \\
  -H "Content-Type: application/json" \\
  -X POST {{ url('/api/v1/posts') }} \\
  -d '{"title": "Hello from the API", "status": "draft"}'</code></pre>
                @else
                    <div class="alert alert-info">
                        The full key was not available on this page — it is only shown once, right after creation.
                        You can still see the key prefix and revoke it below.
                    </div>
                @endif

                <h3 class="h6 mt-4">Key details</h3>
                <dl class="row">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $apiKey->name }}</dd>

                    <dt class="col-sm-4">Prefix</dt>
                    <dd class="col-sm-8"><code>{{ $apiKey->key_prefix }}…</code></dd>

                    <dt class="col-sm-4">Scopes</dt>
                    <dd class="col-sm-8">
                        @forelse($apiKey->scopes ?? [] as $scope)
                            <span class="badge bg-secondary me-1">{{ $scope }}</span>
                        @empty
                            <span class="text-muted small">Read-only (no scopes)</span>
                        @endforelse
                    </dd>

                    <dt class="col-sm-4">Created</dt>
                    <dd class="col-sm-8">{{ $apiKey->created_at->format('Y-m-d H:i') }}</dd>

                    <dt class="col-sm-4">Expires</dt>
                    <dd class="col-sm-8">{{ $apiKey->expires_at?->format('Y-m-d H:i') ?? 'Never' }}</dd>

                    <dt class="col-sm-4">Last used</dt>
                    <dd class="col-sm-8">{{ $apiKey->last_used_at?->diffForHumans() ?? 'Never' }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($apiKey->revoked_at)
                            <span class="badge bg-danger">Revoked</span>
                        @elseif($apiKey->expires_at && $apiKey->expires_at->isPast())
                            <span class="badge bg-warning text-dark">Expired</span>
                        @else
                            <span class="badge bg-success">Active</span>
                        @endif
                    </dd>
                </dl>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('admin.api-keys.index') }}" class="btn btn-primary">Back to API Keys</a>
                    @if(!$apiKey->revoked_at)
                        <form method="POST" action="{{ route('admin.api-keys.destroy', $apiKey) }}"
                              onsubmit="return confirm('Revoke this API key? This cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Revoke key</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyKey() {
            const input = document.getElementById('fullKey');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                const btn = event.target;
                const original = btn.textContent;
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = original, 1500);
            });
        }
    </script>
@endsection
