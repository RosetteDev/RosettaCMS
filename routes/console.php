<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Post;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('posts:publish-due', function () {
    $count = Post::where('status', 'pending')
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->update(['status' => 'published']);

    $this->info("Published {$count} scheduled post(s).");
})->purpose('Publish posts whose scheduled publish date has arrived');

Artisan::command('backups:restore {backup : ID of the backup to restore} {--force : Restore without confirmation (required for destructive restores)}', function () {
    if (!$this->option('force')) {
        $this->error('Restore replaces the current database and media library. Pass --force to confirm.');
        return 1;
    }

    $backup = \App\Models\Backup::find($this->argument('backup'));

    if (!$backup) {
        $this->error('Backup not found.');
        return 1;
    }

    $this->warn("Restoring backup: {$backup->name}");

    app(\App\Services\BackupService::class)->restore($backup);

    $this->info("Backup {$backup->name} restored.");
    return 0;
})->purpose('Restore a backup (destructive: replaces database and media)');

Artisan::command('backups:snapshot {name? : Optional label for the snapshot}', function () {
    $name = $this->argument('name') ?: 'Snapshot';

    $backup = app(\App\Services\BackupService::class)->create();

    // Re-label the backup so it is identifiable as a snapshot.
    $backup->update(['name' => $name . ' ' . now()->format('Y-m-d H:i')]);

    $this->info("Snapshot created: {$backup->name} ({$backup->human_size}).");
    return 0;
})->purpose('Create a snapshot backup for testing theme changes');

Artisan::command('backups:create', function () {
    if (!filter_var(env('BACKUP_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
        $this->info('Backups are disabled (BACKUP_ENABLED=false).');
        return;
    }

    $backup = app(\App\Services\BackupService::class)->create();

    $this->info("Backup created: {$backup->name} ({$backup->human_size}).");
})->purpose('Create a scheduled backup of the database and media library');
