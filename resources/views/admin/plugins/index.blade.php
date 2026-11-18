@extends('admin.layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">Plugins</h1>
            <p class="text-muted mb-0">Built-in Rosetta modules and third-party WordPress plugins</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Built-in Modules</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Plugin</th>
                        <th>Version</th>
                        <th>Author</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($builtIn as $name => $plugin)
                        @php($enabled = array_key_exists($name, $activeBuiltIn))
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $plugin->getName() }}</div>
                                <div class="text-muted small">{{ $plugin->getDescription() }}</div>
                                <div class="text-muted small"><code>{{ $plugin->getKey() }}</code></div>
                            </td>
                            <td>{{ $plugin->getVersion() }}</td>
                            <td>{{ $plugin->getAuthor() ?: '—' }}</td>
                            <td class="text-end">
                                <span class="badge {{ $enabled ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $enabled ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No built-in plugins discovered.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-muted small">
            Built-in modules are enabled in <code>config/plugins.php</code>.
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">WordPress Plugins</h5>
                <div class="text-muted small">Drop a plugin folder into one of the directories below, then activate it.</div>
            </div>
            <span class="badge bg-light text-dark border">{{ count($wordpress) }} found</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Plugin</th>
                        <th>Version</th>
                        <th>Author</th>
                        <th class="text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wordpress as $plugin)
                        @php($enabled = in_array($plugin['id'], $activeWordPress, true))
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $plugin['name'] }}</div>
                                @if($plugin['description'])
                                    <div class="text-muted small">{{ $plugin['description'] }}</div>
                                @endif
                                <div class="text-muted small"><code>{{ str_replace(base_path() . '/', '', $plugin['file']) }}</code></div>
                            </td>
                            <td>{{ $plugin['version'] }}</td>
                            <td>
                                @if($plugin['author_uri'])
                                    <a href="{{ $plugin['author_uri'] }}" target="_blank" rel="noopener">{{ $plugin['author'] ?: 'Unknown' }}</a>
                                @else
                                    {{ $plugin['author'] ?: 'Unknown' }}
                                @endif
                            </td>
                            <td class="text-end">
                                @if($enabled)
                                    <form action="{{ route('admin.plugins.deactivate', $plugin['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.plugins.activate', $plugin['id']) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Activate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                <div class="text-muted mb-2">No WordPress plugins found.</div>
                                <div class="small text-muted">
                                    Scanned:
                                    @foreach($paths as $path)
                                        <code>{{ str_replace(base_path() . '/', '', $path) }}</code>@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-muted small">
            Plugins use the standard WordPress header format and the Rosetta WordPress
            compatibility API (hooks, filters, options, escaping helpers and shortcodes).
        </div>
    </div>
@endsection
