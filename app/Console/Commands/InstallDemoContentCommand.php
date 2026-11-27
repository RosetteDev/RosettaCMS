<?php

namespace App\Console\Commands;

use App\Services\DemoContentService;
use Illuminate\Console\Command;

/**
 * Install demo content for a theme.
 *
 * Usage: php artisan themes:install-demo-content {slug}
 *
 * Reads themes/{slug}/demo-content.json and creates published posts for the
 * active site. Posts whose slug already exists are skipped.
 */
class InstallDemoContentCommand extends Command
{
    protected $signature = 'themes:install-demo-content
                            {slug : Theme slug (e.g. business, photography, blog, magazine)}';

    protected $description = 'Install sample demo posts for a theme (themes/{slug}/demo-content.json)';

    public function handle(): int
    {
        $slug = $this->argument('slug');

        $result = DemoContentService::install($slug);

        if ($result['error']) {
            $this->error($result['error']);
            return 1;
        }

        $this->info("Installed {$result['created']} demo post(s) for '{$slug}' ({$result['skipped']} skipped).");

        return 0;
    }
}
