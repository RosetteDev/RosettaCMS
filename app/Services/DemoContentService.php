<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\File;

/**
 * Installs sample demo posts for a theme.
 *
 * Used by the themes:install-demo-content artisan command and the admin
 * theme page's "Install demo content" button.
 */
class DemoContentService
{
    /**
     * Install demo posts for the given theme.
     *
     * @param  string  $slug  Theme slug (themes/{slug}/demo-content.json)
     * @param  int|null  $siteId  Site to install into (defaults to the first site)
     * @return array{created: int, skipped: int, error: ?string}
     */
    public static function install(string $slug, ?int $siteId = null): array
    {
        $jsonPath = base_path("themes/{$slug}/demo-content.json");

        if (!File::exists($jsonPath)) {
            return ['created' => 0, 'skipped' => 0, 'error' => "No demo content found for theme '{$slug}'."];
        }

        $data = json_decode(File::get($jsonPath), true);

        if (!is_array($data) || empty($data['posts']) || !is_array($data['posts'])) {
            return ['created' => 0, 'skipped' => 0, 'error' => "Demo content for '{$slug}' is invalid."];
        }

        $site = $siteId ? Site::find($siteId) : Site::first();

        if (!$site) {
            return ['created' => 0, 'skipped' => 0, 'error' => 'No site found. Run the installer first.'];
        }

        $actor = auth()->check()
            ? auth()->user()
            : (User::first());

        $created = 0;
        $skipped = 0;

        foreach ($data['posts'] as $post) {
            $postSlug = trim((string) ($post['slug'] ?? ''));
            $title = trim((string) ($post['title'] ?? ''));

            if ($postSlug === '' || $title === '') {
                $skipped++;
                continue;
            }

            $exists = Post::where('site_id', $site->id)
                ->where('slug', $postSlug)
                ->where('post_type', 'post')
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Post::create([
                'site_id' => $site->id,
                'user_id' => $actor?->id,
                'title' => $title,
                'slug' => $postSlug,
                'content' => $post['content'] ?? null,
                'excerpt' => $post['excerpt'] ?? null,
                'meta' => ['demo_content' => $slug],
                'post_type' => 'post',
                'status' => 'published',
                'published_at' => now(),
                'comment_status' => 'open',
            ]);

            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped, 'error' => null];
    }
}
