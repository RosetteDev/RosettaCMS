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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">Third-Party Ecosystem</h5>
                    <div class="text-muted small">Plugins with a <code>plugin.json</code> manifest in <code>plugins/</code>. Permissions are not granted until you approve them below.</div>
                </div>
                <span class="badge bg-light text-dark border">{{ count($ecosystemPlugins) }} found</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Plugin</th>
                        <th>Version</th>
                        <th>Permissions</th>
                        <th class="text-end">Security</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ecosystemPlugins as $plugin)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $plugin['name'] }}</div>
                                @if($plugin['description'])
                                    <div class="text-muted small">{{ $plugin['description'] }}</div>
                                @endif
                                <div class="text-muted small"><code>{{ $plugin['slug'] }}</code></div>
                                @if($plugin['errors'])
                                    <div class="text-danger small mt-1">
                                        @foreach($plugin['errors'] as $error)
                                            <div>&#9888; {{ $error }}</div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>{{ $plugin['version'] }}</td>
                            <td>
                                @if(empty($plugin['permissions']))
                                    <span class="text-muted small">None requested</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($plugin['permissions'] as $permission)
                                            @if(in_array($permission, $plugin['approved_permissions'], true))
                                                <form action="{{ route('admin.plugins.permissions.revoke', [$plugin['slug'], $permission]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="badge bg-success border-0" title="Click to revoke">{{ $permission }} &#10003;</button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.plugins.permissions.approve', [$plugin['slug'], $permission]) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="badge bg-warning text-dark border-0" title="Click to approve">{{ $permission }} &#9888;</button>
                                                </form>
                                            @endif
                                        @endforeach
                                    </div>
                                    @if(!empty($plugin['missing_permissions']))
                                        <div class="text-muted small mt-1">Pending approval</div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-end">
                                @php($critical = count(array_filter($plugin['findings'], fn ($f) => $f['severity'] === 'critical')))
                                @php($warnings = count(array_filter($plugin['findings'], fn ($f) => $f['severity'] === 'warning')))
                                @if($critical > 0)
                                    <span class="badge bg-danger">{{ $critical }} critical</span>
                                @elseif($warnings > 0)
                                    <span class="badge bg-warning text-dark">{{ $warnings }} warning(s)</span>
                                @elseif(count($plugin['findings']) > 0)
                                    <span class="badge bg-light text-dark">{{ count($plugin['findings']) }} info</span>
                                @else
                                    <span class="badge bg-success">Clean</span>
                                @endif
                                @if(!empty($plugin['findings']))
                                    <details class="mt-2">
                                        <summary class="text-muted small">Details</summary>
                                        <ul class="list-unstyled small mt-1 mb-0">
                                            @foreach(array_slice($plugin['findings'], 0, 5) as $finding)
                                                <li><code>{{ $finding['file'] }}:{{ $finding['line'] }}</code> — {{ $finding['label'] }}</li>
                                            @endforeach
                                            @if(count($plugin['findings']) > 5)
                                                <li class="text-muted">+ {{ count($plugin['findings']) - 5 }} more</li>
                                            @endif
                                        </ul>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <div class="mb-2">No third-party plugins with a <code>plugin.json</code> manifest found.</div>
                                <div class="small">Drop a plugin folder with a manifest into <code>plugins/</code> — see the manifest format in <code>app/Services/PluginEcosystemService.php</code>.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-muted small">
            Security findings are static-analysis heuristics, not a sandbox — always review untrusted code before approving permissions.
        </div>
    </div>
@endsection
