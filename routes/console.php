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
