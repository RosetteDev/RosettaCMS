@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Backups</h1>
            <p>Scheduled snapshots of the database and media library. Stored on the <strong>{{ $disk }}</strong> disk.</p>
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

    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem;">
        <div>
            <form method="POST" action="{{ route('admin.backups.store') }}" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-database me-1"></i> Create Backup Now
                </button>
            </form>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.backups.export', ['format' => 'json']) }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-code me-1"></i> Export JSON
            </a>
            <a href="{{ route('admin.backups.export', ['format' => 'csv']) }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <div style="padding: .5rem 0 1rem; display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;">
        <form method="POST" action="{{ route('admin.backups.import') }}" enctype="multipart/form-data"
              style="display: flex; align-items: center; gap: .5rem; flex-wrap: wrap;">
            @csrf
            <input type="file" name="file" accept=".json,.csv" required
                   class="form-control form-control-sm" style="max-width: 320px;">
            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-file-import me-1"></i> Import content
            </button>
        </form>
        <small class="text-muted">JSON restores posts, pages, categories and tags; CSV restores posts/pages only.</small>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-1"></i>
        A backup archives every table (except ephemeral sessions/cache/jobs) plus all media files.
        Restoring replaces the current database and media library. Exports contain posts, pages, categories and tags.
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if($backups->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-database fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No backups yet. Click <strong>Create Backup Now</strong> to take your first one.</p>
            </div>
        @else
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Size</th>
                        <th>Tables</th>
                        <th>Media</th>
                        <th>Created by</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backups as $backup)
                        <tr>
                            <td><strong>{{ $backup->name }}</strong></td>
                            <td class="small">{{ $backup->created_at->format('Y-m-d H:i') }}</td>
                            <td class="small">{{ $backup->human_size }}</td>
                            <td class="small">{{ $backup->tables_count }}</td>
                            <td class="small">{{ $backup->media_count }}</td>
                            <td class="small">{{ $backup->user?->name ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-sm btn-outline-secondary" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-primary" data-restore-backup="{{ $backup->id }}" title="Restore">
                                    <i class="fas fa-undo"></i>
                                </button>
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}" style="display:inline"
                                      onsubmit="return confirm('Delete this backup? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div style="padding-top: 1rem;">
        {{ $backups->links() }}
    </div>

    <!-- Restore confirmation modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Restore backup?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>This will replace the current database and media library with the contents of <strong id="restoreBackupName"></strong>.</p>
                    <p class="text-danger mb-0"><strong>This cannot be undone.</strong> Make sure you have a recent backup first.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="restoreForm" action="">
                        @csrf
                        <input type="hidden" name="confirm" value="restore">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-undo me-1"></i> Restore this backup
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const restoreModalEl = document.getElementById('restoreModal');
        const restoreModal = new bootstrap.Modal(restoreModalEl);
        const restoreForm = document.getElementById('restoreForm');
        const restoreBackupName = document.getElementById('restoreBackupName');

        document.querySelectorAll('[data-restore-backup]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.getAttribute('data-restore-backup');
                const row = btn.closest('tr');
                const name = row ? row.querySelector('td strong').textContent : ('Backup #' + id);
                restoreBackupName.textContent = name;
                restoreForm.action = '{{ url('/admin/backups') }}/' + id + '/restore';
                restoreModal.show();
            });
        });
    </script>
@endsection
