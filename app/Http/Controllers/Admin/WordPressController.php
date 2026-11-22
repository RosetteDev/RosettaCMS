<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use RosettaCMS\Plugins\WordPress\ImportExport\WXRExporter;
use RosettaCMS\Plugins\WordPress\ImportExport\WXRImporter;
use RosettaCMS\Plugins\WordPress\Migrations\WordPressMigrationTool;

class WordPressController extends Controller
{
    protected $exporter;
    protected $importer;
    protected $migrator;

    public function __construct()
    {
        $this->exporter = new WXRExporter();
        $this->importer = new WXRImporter();
        $this->migrator = new WordPressMigrationTool();
    }

    /**
     * Show WordPress compatibility index
     */
    public function index()
    {
        return view('admin.wordpress.index', [
            'exportedFiles' => $this->exporter->getExportedFiles(),
            'importedFiles' => $this->importer->getImportedFiles(),
            'dbStats' => $this->migrator->getDatabaseStats(),
        ]);
    }

    /**
     * Export WordPress content
     */
    public function export(Request $request)
    {
        $postTypes = $request->input('post_types', ['post', 'page']);

        try {
            $filepath = $this->exporter->exportPostTypes($postTypes);
            return $this->exporter->downloadExport(basename($filepath));
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete export file
     */
    public function deleteExport(Request $request)
    {
        $filename = $request->input('filename');

        if ($this->exporter->deleteExport($filename)) {
            return back()->with('success', 'Export deleted successfully');
        }

        return back()->with('error', 'Failed to delete export');
    }

    /**
     * Upload and import WordPress file
     */
    public function uploadImport(Request $request)
    {
        $request->validate([
            'wxr_file' => 'required|file|mimes:xml|max:10240',
        ]);

        try {
            $filepath = $this->importer->handleUpload($request->file('wxr_file'));
            $result = $this->importer->import($filepath);

            if ($result['success']) {
                return back()->with('success', "Import completed successfully. {$result['migrated']} items processed.");
            }

            return back()->with('error', 'Import completed with errors. Check logs for details.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show migration tools
     */
    public function migrate()
    {
        return view('admin.wordpress.migrate', [
            'canConnect' => $this->migrator->checkConnection(),
            'dbStats' => $this->migrator->getDatabaseStats(),
            'prefix' => config('rosetta.wordpress_db_prefix', 'wp_'),
        ]);
    }

    /**
     * Run migration
     */
    public function runMigration(Request $request)
    {
        $request->validate([
            'steps' => 'required|array',
            'steps.*' => 'in:users,terms,posts,comments,metadata',
        ]);

        $steps = $request->input('steps');
        $results = [];

        foreach ($steps as $step) {
            $method = "migrate{$step}";
            if (method_exists($this->migrator, $method)) {
                $results[$step] = $this->migrator->$method();
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Check WordPress database connection
     */
    public function checkConnection()
    {
        $connected = $this->migrator->checkConnection();

        return response()->json([
            'connected' => $connected,
            'stats' => $connected ? $this->migrator->getDatabaseStats() : null,
        ]);
    }

    /**
     * Download all content export
     */
    public function exportAll()
    {
        try {
            $filepath = $this->exporter->exportAll();
            return $this->exporter->downloadExport(basename($filepath));
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}
