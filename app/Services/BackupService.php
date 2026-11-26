<?php

namespace App\Services;

use App\Models\Backup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupService
{
    /**
     * Tables that are not part of a restore: runtime/ephemeral state.
     */
    protected const EXCLUDED_TABLES = [
        'backups',
        'cache',
        'cache_locks',
        'jobs',
        'failed_jobs',
        'sessions',
        'password_reset_tokens',
    ];

    /**
     * Tables in dependency order (parents first). Restoring inserts in this
     * order and deletes in reverse, which keeps foreign keys valid whether or
     * not FK enforcement is toggled (toggling is a no-op inside a transaction,
     * which is how tests wrap each case).
     */
    protected const DEPENDENCY_ORDER = [
        'users',
        'sites',
        'roles',
        'languages',
        'categories',
        'tags',
        'taxonomies',
        'terms',
        'site_settings',
        'menus',
        'posts',
        'media',
        'comments',
        'blocks',
        'custom_widgets',
        'post_revisions',
        'language_content',
        'api_keys',
        'category_post',
        'tag_post',
        'post_term',
        'role_user',
        'menu_items',
        'migrations',
    ];

    /**
     * The disk used to store backup archives.
     * Defaults to the local disk (storage/app/private/backups). Set
     * BACKUP_DISK=s3 (plus AWS_* env) to store offsite.
     */
    public function disk(): string
    {
        return (string) env('BACKUP_DISK', 'local');
    }

    /**
     * Create a new backup archive.
     */
    public function create(?User $actor = null): Backup
    {
        $tables = $this->restoreTables();
        $database = $this->dumpDatabase($tables);
        $mediaFiles = $this->collectMediaFiles();

        $manifest = [
            'name' => 'Backup',
            'created_at' => now()->toIso8601String(),
            'driver' => DB::connection()->getDriverName(),
            'tables' => array_keys($database['tables']),
            'media_count' => count($mediaFiles),
            'app_name' => config('app.name'),
        ];

        $disk = $this->disk();
        $name = 'backup-' . now()->format('Y-m-d-His') . '-' . Str::lower(Str::random(6)) . '.zip';
        $path = 'backups/' . $name;

        $zip = new \ZipArchive();
        $tmpZip = tempnam(sys_get_temp_dir(), 'backup-') . '.zip';

        if ($zip->open($tmpZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create backup archive.');
        }

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->addFromString('database.json', json_encode($database, JSON_INVALID_UTF8_SUBSTITUTE));

        $publicRoot = Storage::disk('public')->path('');

        foreach ($mediaFiles as $relativePath) {
            $absolute = $publicRoot . '/' . $relativePath;

            if (is_file($absolute)) {
                // Strip the leading 'media/' so the archive stores files as
                // media/<relative path inside the media library>.
                $archivePath = 'media/' . ltrim(substr($relativePath, strlen('media/')), '/');
                $zip->addFile($absolute, $archivePath);
            }
        }

        $zip->close();

        $size = filesize($tmpZip);
        Storage::disk($disk)->put($path, file_get_contents($tmpZip));
        @unlink($tmpZip);

        return Backup::create([
            'name' => $manifest['name'] . ' ' . now()->format('Y-m-d H:i'),
            'disk' => $disk,
            'path' => $path,
            'size_bytes' => $size,
            'tables_count' => count($database['tables']),
            'media_count' => count($mediaFiles),
            'manifest' => $manifest,
            'created_by' => $actor?->id,
        ]);
    }

    /**
     * Restore a backup: replaces the database contents (minus ephemeral
     * tables) and the media library with the contents of the archive.
     */
    public function restore(Backup $backup): void
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'restore-') . '.zip';
        file_put_contents($zipPath, Storage::disk($backup->disk)->get($backup->path));

        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Could not open backup archive.');
        }

        $tmpDir = sys_get_temp_dir() . '/restore-' . Str::random(12);
        mkdir($tmpDir, 0755, true);
        $zip->extractTo($tmpDir);
        $zip->close();
        @unlink($zipPath);

        try {
            $manifest = json_decode(file_get_contents($tmpDir . '/manifest.json'), true);
            $database = json_decode(file_get_contents($tmpDir . '/database.json'), true);

            if (!is_array($database['tables'] ?? null)) {
                throw new \RuntimeException('Backup is missing a database dump.');
            }

            $this->disableForeignKeys();

            try {
                DB::transaction(function () use ($database) {
                    $ordered = $this->orderedTables($database['tables']);

                    // Delete in reverse dependency order, then insert in
                    // dependency order (parents first).
                    foreach (array_reverse($ordered) as $table) {
                        DB::table($table)->delete();
                    }

                    foreach ($ordered as $table) {
                        foreach ($database['tables'][$table] as $row) {
                            DB::table($table)->insert($row);
                        }
                    }
                });
            } finally {
                $this->enableForeignKeys();
            }

            $this->restoreMedia($tmpDir . '/media');

            // Clear cached/resolved state so the restored data is picked up.
            try {
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                \Illuminate\Support\Facades\Artisan::call('config:clear');
            } catch (\Throwable) {
                // Best-effort: not every environment supports cache:clear.
            }
        } finally {
            $this->deleteDirectory($tmpDir);
        }
    }

    /**
     * Delete the archive and its record.
     */
    public function delete(Backup $backup): void
    {
        try {
            Storage::disk($backup->disk)->delete($backup->path);
        } catch (\Throwable) {
            // Already gone.
        }

        $backup->delete();
    }

    /**
     * Export content (posts, pages, categories, tags) as a structured array.
     */
    public function exportContent(): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'posts' => DB::table('posts')
                ->where('post_type', 'post')
                ->get()
                ->toArray(),
            'pages' => DB::table('posts')
                ->where('post_type', 'page')
                ->get()
                ->toArray(),
            'categories' => DB::table('categories')->get()->toArray(),
            'tags' => DB::table('tags')->get()->toArray(),
        ];
    }

    /**
     * Export the content as CSV (one row per post/page).
     */
    public function exportCsv(): string
    {
        $rows = DB::table('posts')
            ->whereIn('post_type', ['post', 'page'])
            ->orderBy('post_type')->orderBy('title')
            ->get();

        $handle = fopen('php://memory', 'r+');

        fputcsv($handle, ['type', 'title', 'slug', 'status', 'published_at', 'created_at']);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->post_type,
                $row->title,
                $row->slug,
                $row->status,
                $row->published_at,
                $row->created_at,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Return the dumped tables in dependency order, with any table not in the
     * known list appended at the end (and deleted first).
     */
    protected function orderedTables(array $tables): array
    {
        $names = array_keys($tables);
        $ordered = [];

        foreach (self::DEPENDENCY_ORDER as $table) {
            if (array_key_exists($table, $tables)) {
                $ordered[] = $table;
            }
        }

        foreach ($names as $table) {
            if (!in_array($table, $ordered, true) && !in_array($table, self::EXCLUDED_TABLES, true)) {
                $ordered[] = $table;
            }
        }

        return $ordered;
    }

    protected function restoreTables(): array
    {
        $tables = [];

        foreach (Schema::getTables() as $table) {
            $name = $table['name'] ?? null;

            if (!$name || in_array($name, self::EXCLUDED_TABLES, true)) {
                continue;
            }

            $tables[] = $name;
        }

        return $tables;
    }

    protected function dumpDatabase(array $tables): array
    {
        $database = ['tables' => []];

        foreach ($tables as $table) {
            $database['tables'][$table] = DB::table($table)->get()->toArray();
        }

        return $database;
    }

    protected function collectMediaFiles(): array
    {
        $disk = Storage::disk('public');
        $files = [];

        if (!$disk->exists('media')) {
            return $files;
        }

        $list = $disk->allFiles('media');

        foreach ($list as $file) {
            $files[] = $file;
        }

        return $files;
    }

    protected function disableForeignKeys(): void
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }
        } catch (\Throwable) {
            // Best-effort.
        }
    }

    protected function enableForeignKeys(): void
    {
        try {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }
        } catch (\Throwable) {
            // Best-effort.
        }
    }

    protected function restoreMedia(string $mediaDir): void
    {
        if (!is_dir($mediaDir)) {
            return;
        }

        $publicDisk = Storage::disk('public');
        $publicRoot = $publicDisk->path('');

        $existing = $publicRoot . '/media';
        if (is_dir($existing)) {
            $this->deleteDirectory($existing);
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($mediaDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $relative = substr($file->getPathname(), strlen($mediaDir) + 1);
            $target = $publicRoot . '/media/' . $relative;

            if ($file->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
                continue;
            }

            $targetDir = dirname($target);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            copy($file->getPathname(), $target);
        }
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}
