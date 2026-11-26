@extends('admin.layouts.app')

@section('title', 'WordPress Compatibility')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 mb-1">WordPress Compatibility</h1>
            <p class="text-muted mb-0">Import/export WordPress content and migrate from an existing WordPress site.</p>
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

    @unless($isConfigured)
        <div class="alert alert-warning border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-plug fa-lg me-3 mt-1"></i>
                <div>
                    <h2 class="h6 mb-1">No WordPress database connection configured</h2>
                    <p class="mb-1 small">
                        Export and import still work without a WordPress connection. To migrate data from an existing
                        WordPress site, add a <code>wordpress</code> connection to <code>config/database.php</code>
                        and set <code>rosetta.wordpress_db_prefix</code> (default <code>wp_</code>).
                    </p>
                </div>
            </div>
        </div>
    @endunless

    <div class="row g-4">
        {{-- Export --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0"><i class="fas fa-file-export text-primary me-2"></i>Export Content</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Download a WordPress WXR export of your content.</p>

                    <form id="exportForm" class="mb-3">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="post_types[]" value="post" id="expPost" checked>
                            <label class="form-check-label" for="expPost">Posts</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="post_types[]" value="page" id="expPage" checked>
                            <label class="form-check-label" for="expPage">Pages</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="post_types[]" value="attachment" id="expAttach">
                            <label class="form-check-label" for="expAttach">Attachments</label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-download me-1"></i>Export selected
                        </button>
                        <a href="{{ route('admin.wordpress.export-all') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-archive me-1"></i>Export everything
                        </a>
                    </form>

                    @if(count($exportedFiles))
                        <div class="border-top pt-3">
                            <h3 class="h6 mb-2">Previous exports</h3>
                            <div class="list-group list-group-flush">
                                @foreach($exportedFiles as $file)
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                        <div class="text-truncate">
                                            <code class="small">{{ $file['name'] }}</code>
                                            <span class="text-muted small ms-2">{{ round($file['size'] / 1024, 2) }} KB</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteExport('{{ $file['name'] }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Import --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0"><i class="fas fa-file-import text-primary me-2"></i>Import Content</h2>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Import a WordPress WXR export.</p>

                    <form action="{{ route('admin.wordpress.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="wxr_file" class="form-label">WXR file</label>
                            <input class="form-control" type="file" name="wxr_file" id="wxr_file" accept=".xml" required>
                            <div class="form-text">Maximum file size: 10MB</div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload me-1"></i>Import WordPress content
                        </button>
                    </form>

                    @if(count($importedFiles))
                        <div class="border-top pt-3 mt-3">
                            <h3 class="h6 mb-2">Uploaded files</h3>
                            <div class="list-group list-group-flush">
                                @foreach($importedFiles as $file)
                                    <div class="list-group-item px-0 py-2 d-flex justify-content-between">
                                        <code class="small">{{ $file['name'] }}</code>
                                        <span class="text-muted small">{{ round($file['size'] / 1024, 2) }} KB</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Migration --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h5 mb-0"><i class="fas fa-database text-primary me-2"></i>Migrate from WordPress database</h2>
                </div>
                <div class="card-body">
                    @if($isConfigured)
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                            <span class="badge {{ $canConnect ? 'bg-success' : 'bg-danger' }} d-inline-flex align-items-center">
                                <i class="fas {{ $canConnect ? 'fa-circle-check' : 'fa-circle-xmark' }} me-1"></i>
                                {{ $canConnect ? 'Connected' : 'Not reachable' }}
                            </span>
                            <span class="text-muted small">Table prefix: <code>{{ $prefix }}</code></span>
                        </div>

                        @if($dbStats && empty($dbStats['error']))
                            <div class="row g-3 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="bg-light rounded p-3 text-center">
                                        <div class="h4 mb-0">{{ $dbStats['users'] ?? 0 }}</div>
                                        <div class="small text-muted">Users</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light rounded p-3 text-center">
                                        <div class="h4 mb-0">{{ $dbStats['posts'] ?? 0 }}</div>
                                        <div class="small text-muted">Posts</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light rounded p-3 text-center">
                                        <div class="h4 mb-0">{{ $dbStats['terms'] ?? 0 }}</div>
                                        <div class="small text-muted">Terms</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light rounded p-3 text-center">
                                        <div class="h4 mb-0">{{ $dbStats['comments'] ?? 0 }}</div>
                                        <div class="small text-muted">Comments</div>
                                    </div>
                                </div>
                            </div>
                        @elseif($dbStats && !empty($dbStats['error']))
                            <div class="alert alert-danger py-2 small">{{ $dbStats['error'] }}</div>
                        @endif

                        @if($canConnect)
                            <form id="migrateForm" onsubmit="return runMigration(event)">
                                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                                    <label class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="steps[]" value="users" checked> Users
                                    </label>
                                    <label class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="steps[]" value="terms" checked> Terms
                                    </label>
                                    <label class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="steps[]" value="posts" checked> Posts &amp; pages
                                    </label>
                                    <label class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="steps[]" value="comments" checked> Comments
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-play me-1"></i>Run migration
                                </button>
                            </form>
                            <div id="migrateResult" class="mt-3 small"></div>
                        @endif
                    @else
                        <p class="text-muted mb-0">
                            Configure the <code>wordpress</code> connection in <code>config/database.php</code> to migrate
                            content from an existing WordPress site.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        const exportForm = document.getElementById('exportForm');
        if (exportForm) {
            exportForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const postTypes = Array.from(exportForm.querySelectorAll('input[name="post_types[]"]:checked')).map(cb => cb.value);
                if (postTypes.length === 0) {
                    alert('Please select at least one post type.');
                    return;
                }
                const params = new URLSearchParams();
                postTypes.forEach(type => params.append('post_types[]', type));
                window.location.href = '{{ route("admin.wordpress.export") }}?' + params.toString();
            });
        }

        function deleteExport(filename) {
            if (!confirm('Are you sure you want to delete this export?')) return;
            fetch('{{ route("admin.wordpress.export.delete") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ filename: filename })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete export.');
                }
            })
            .catch(error => alert('Failed to delete export: ' + error.message));
        }

        function runMigration(event) {
            event.preventDefault();
            const form = document.getElementById('migrateForm');
            const steps = Array.from(form.querySelectorAll('input[name="steps[]"]:checked')).map(cb => cb.value);
            const resultBox = document.getElementById('migrateResult');

            if (steps.length === 0) {
                alert('Please select at least one migration step.');
                return false;
            }

            resultBox.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Running migration…</span>';

            fetch('{{ route("admin.wordpress.migrate.run") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ steps: steps })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<span class="text-success"><i class="fas fa-circle-check me-1"></i>Migration completed.</span><ul class="mb-0 mt-2">';
                    for (const [step, result] of Object.entries(data.results || {})) {
                        const migrated = result && typeof result === 'object' ? (result.migrated ?? 0) : 0;
                        html += `<li><code>${step}</code>: ${migrated} item(s)</li>`;
                    }
                    html += '</ul>';
                    resultBox.innerHTML = html;
                } else {
                    resultBox.innerHTML = '<span class="text-danger"><i class="fas fa-circle-xmark me-1"></i>Migration failed.</span>';
                }
            })
            .catch(error => {
                resultBox.innerHTML = '<span class="text-danger"><i class="fas fa-circle-xmark me-1"></i>Migration failed: ' + error.message + '</span>';
            });
            return false;
        }
    </script>
@endsection
