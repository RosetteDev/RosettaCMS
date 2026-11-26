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

Artisan::command('backups:create', function () {
    if (!filter_var(env('BACKUP_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
        $this->info('Backups are disabled (BACKUP_ENABLED=false).');
        return;
    }

    $backup = app(\App\Services\BackupService::class)->create();

    $this->info("Backup created: {$backup->name} ({$backup->human_size}).");
})->purpose('Create a scheduled backup of the database and media library');
