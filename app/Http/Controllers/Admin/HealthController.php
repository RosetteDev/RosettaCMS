<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Backup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthController extends Controller
{
    /**
     * Run the site health checks and show the report.
     */
    public function index()
    {
        $checks = [];

        // Database connectivity.
        try {
            DB::select('select 1');
            $checks['database'] = ['label' => 'Database connection', 'ok' => true, 'detail' => 'select 1 succeeded'];
        } catch (\Throwable $e) {
            $checks['database'] = ['label' => 'Database connection', 'ok' => false, 'detail' => $e->getMessage()];
        }

        // Storage writable.
        try {
            $storagePath = storage_path('app');
            $writable = is_writable($storagePath);
            $checks['storage'] = [
                'label' => 'Storage writable',
                'ok' => $writable,
                'detail' => $writable ? $storagePath : "Not writable: {$storagePath}",
            ];
        } catch (\Throwable $e) {
            $checks['storage'] = ['label' => 'Storage writable', 'ok' => false, 'detail' => $e->getMessage()];
        }

        // Mail configured.
        $mailDriver = config('mail.default');
        $mailConfig = $mailDriver ? config("mail.mailers.{$mailDriver}") : null;
        $mailOk = !empty($mailDriver) && is_array($mailConfig);
        $checks['mail'] = [
            'label' => 'Mail configured',
            'ok' => $mailOk,
            'detail' => 'Driver: ' . ($mailDriver ?? 'not set'),
        ];

        // Latest backup exists.
        try {
            $latestBackup = Backup::latest()->first();
            $checks['backup'] = [
                'label' => 'Latest backup',
                'ok' => $latestBackup !== null,
                'detail' => $latestBackup
                    ? "{$latestBackup->name} ({$latestBackup->human_size}, {$latestBackup->created_at->diffForHumans()})"
                    : 'No backups found — run backups:create',
            ];
        } catch (\Throwable $e) {
            $checks['backup'] = ['label' => 'Latest backup', 'ok' => false, 'detail' => $e->getMessage()];
        }

        // Security headers middleware is in the pipeline.
        try {
            $middleware = app('router')->getMiddleware();
            $hasHeaders = collect($middleware)->contains(fn ($m) => is_string($m) ? str_contains($m, 'SecurityHeaders') : false)
                || class_exists(\App\Http\Middleware\SecurityHeaders::class);
            $checks['headers'] = [
                'label' => 'Security headers',
                'ok' => $hasHeaders,
                'detail' => $hasHeaders ? 'SecurityHeaders middleware registered' : 'SecurityHeaders middleware missing',
            ];
        } catch (\Throwable $e) {
            $checks['headers'] = ['label' => 'Security headers', 'ok' => false, 'detail' => $e->getMessage()];
        }

        // HTTPS (public URL should be https in production).
        $appUrl = config('app.url');
        $isHttps = str_starts_with((string) $appUrl, 'https://');
        $checks['https'] = [
            'label' => 'HTTPS (APP_URL)',
            'ok' => $isHttps,
            'detail' => $appUrl,
        ];

        // PHP version.
        $phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
        $checks['php'] = [
            'label' => 'PHP version',
            'ok' => $phpOk,
            'detail' => PHP_VERSION . ($phpOk ? ' (supported)' : ' (unsupported, need >= 8.1)'),
        ];

        $allOk = collect($checks)->every(fn ($check) => $check['ok']);

        return view('admin.health.index', compact('checks', 'allOk'));
    }
}
