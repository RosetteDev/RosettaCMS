<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Post;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('posts:publish-due', function () {
    $due = Post::where('status', 'pending')
        ->whereNotNull('published_at')
        ->where('published_at', '<=', now())
        ->get();

    $count = 0;
    $recurring = 0;

    foreach ($due as $post) {
        $post->status = 'published';
        $post->save();
        $count++;

        // A recurring post goes live again on its next occurrence.
        $next = $post->nextOccurrence();

        if ($next) {
            // Bulk update: re-arming is a scheduler detail, not a content
            // change, so it must not fire an `updated` webhook.
            Post::where('id', $post->id)->update([
                'status' => 'pending',
                'published_at' => $next,
            ]);
            $recurring++;
        }
    }

    $this->info("Published {$count} scheduled post(s); re-armed {$recurring} recurring post(s).");
})->purpose('Publish posts whose scheduled publish date has arrived');

Artisan::command('posts:expire', function () {
    $expired = Post::where('status', 'published')
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->get();

    foreach ($expired as $post) {
        // Unpublish to draft: a pending post with a past published_at would
        // be re-published by posts:publish-due on the next run. Bulk update
        // so the scheduler does not fire a content-change webhook.
        Post::where('id', $post->id)->update(['status' => 'draft']);
    }

    $this->info("Expired {$expired->count()} post(s).");
})->purpose('Unpublish posts whose expiry date has arrived');

Artisan::command('posts:digest {--hours=24 : Send posts published in the last N hours}', function () {
    $hours = (int) $this->option('hours');
    $since = now()->subHours($hours);

    $site = \App\Models\Site::first();

    if (!$site) {
        $this->warn('No site configured — nothing to digest.');
        return;
    }

    $settings = \App\Models\SiteSetting::forSite($site);
    $recipient = $settings->admin_email
        ?: $settings->contact_email
        ?: config('mail.from.address');

    if (!$recipient) {
        $this->warn('No admin email configured — nothing to send.');
        return;
    }

    $posts = Post::published()
        ->where('post_type', 'post')
        ->where('published_at', '>=', $since)
        ->orderBy('published_at', 'desc')
        ->get();

    if ($posts->isEmpty()) {
        $this->info('No posts published in the last ' . $hours . ' hours — digest skipped.');
        return;
    }

    $siteName = $settings->site_title ?? $site->name ?? 'your website';

    \Illuminate\Support\Facades\Mail::to($recipient)->send(
        new \App\Mail\PostDigest($siteName, $hours, $posts)
    );

    $this->info("Sent digest with {$posts->count()} post(s) to {$recipient}.");
})->purpose('Email the site admin a digest of recently published posts');

Artisan::command('posts:purge {--days=30 : Permanently delete trashed posts older than this many days}', function () {
    $days = (int) $this->option('days');
    $cutoff = now()->subDays($days);

    $trashed = Post::where('status', 'trash')
        ->where('updated_at', '<=', $cutoff)
        ->get();

    $count = $trashed->count();

    foreach ($trashed as $post) {
        $post->delete();
    }

    $this->info("Purged {$count} trashed post(s) older than {$days} days.");
})->purpose('Permanently delete trashed posts older than the given threshold');

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
