<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function __construct(
        protected BackupService $backups
    ) {
    }

    /**
     * Display a listing of backups.
     */
    public function index()
    {
        $backups = Backup::with('user')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.backups.index', [
            'backups' => $backups,
            'disk' => $this->backups->disk(),
        ]);
    }

    /**
     * Create a new backup.
     */
    public function store(Request $request)
    {
        $backup = $this->backups->create(auth()->user());

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup created: ' . $backup->name . ' (' . $backup->human_size . ').');
    }

    /**
     * Download a backup archive.
     */
    public function download(Backup $backup)
    {
        $disk = $backup->disk;

        abort_unless(\Illuminate\Support\Facades\Storage::disk($disk)->exists($backup->path), 404);

        return \Illuminate\Support\Facades\Storage::disk($disk)->download($backup->path, basename($backup->path));
    }

    /**
     * Restore a backup. Dangerous: replaces database + media contents.
     */
    public function restore(Request $request, Backup $backup)
    {
        $request->validate([
            'confirm' => ['required', 'in:restore'],
        ]);

        try {
            $this->backups->restore($backup);
        } catch (\Throwable $e) {
            return redirect()->route('admin.backups.index')
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup "' . $backup->name . '" restored.');
    }

    /**
     * Delete a backup.
     */
    public function destroy(Backup $backup)
    {
        $this->backups->delete($backup);

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup deleted.');
    }

    /**
     * Export content as JSON or CSV.
     */
    public function export(string $format)
    {
        abort_unless(in_array($format, ['json', 'csv'], true), 404);

        if ($format === 'json') {
            return response()->json($this->backups->exportContent(), 200, [
                'Content-Disposition' => 'attachment; filename="rosetta-content-export.json"',
            ]);
        }

        $csv = $this->backups->exportCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rosetta-content-export.csv"',
        ]);
    }
}
