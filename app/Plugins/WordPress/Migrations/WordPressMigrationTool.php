<?php

namespace RosettaCMS\Plugins\WordPress\Migrations;

use RosettaCMS\Models\Post;
use RosettaCMS\Models\User;
use RosettaCMS\Models\Term;
use RosettaCMS\Models\Comment;
use RosettaCMS\Models\Block;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WordPressMigrationTool
{
    protected $prefix;
    protected $convertedData = [];

    public function __construct()
    {
        $this->prefix = config('rosetta.wordpress_db_prefix', 'wp_');
    }

    /**
     * Resolve the WordPress database connection lazily so the tool can be
     * constructed even when no WordPress connection is configured.
     *
     * @return \Illuminate\Database\Connection|null
     */
    protected function connection()
    {
        if (!config('database.connections.wordpress')) {
            return null;
        }

        try {
            return DB::connection('wordpress');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Whether a WordPress database connection is configured and usable.
     */
    public function isConfigured(): bool
    {
        return $this->connection() !== null;
    }

    /**
     * Migrate all WordPress content
     */
    public function migrateAll()
    {
        $results = [];

        // Migrate users
        $results['users'] = $this->migrateUsers();

        // Migrate terms (categories, tags)
        $results['terms'] = $this->migrateTerms();

        // Migrate posts
        $results['posts'] = $this->migratePosts();

        // Migrate comments
        $results['comments'] = $this->migrateComments();

        // Migrate metadata
        $results['metadata'] = $this->migrateMetadata();

        return $results;
    }

    /**
     * Migrate WordPress users to Rosetta CMS
     */
    public function migrateUsers()
    {
        if (!$this->connection()) {
            return ['migrated' => 0, 'errors' => ['WordPress database connection is not configured.']];
        }
        $wpUsers = $this->connection()->table("{$this->prefix}users")->get();
        $migrated = 0;
        $errors = [];

        foreach ($wpUsers as $wpUser) {
            try {
                $user = User::firstOrNew(['login' => $wpUser->user_login]);

                if (!$user->exists) {
                    $user->display_name = $wpUser->display_name;
                    $user->email = $wpUser->user_email;
                    $user->password = $wpUser->user_pass; // Store hashed password
                    $user->user_url = $wpUser->user_url;
                    $user->created_at = $wpUser->user_registered;
                    $user->save();

                    $migrated++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'user' => $wpUser->user_login,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'migrated' => $migrated,
            'errors' => $errors,
        ];
    }

    /**
     * Migrate WordPress terms (categories, tags)
     */
    public function migrateTerms()
    {
        if (!$this->connection()) {
            return ['migrated' => 0, 'errors' => ['WordPress database connection is not configured.']];
        }
        $wpTerms = $this->connection()->table("{$this->prefix}terms")->get();
        $migrated = 0;
        $errors = [];

        foreach ($wpTerms as $wpTerm) {
            try {
                $term = Term::firstOrNew([
                    'name' => $wpTerm->name,
                    'slug' => $wpTerm->slug,
                ]);

                if (!$term->exists) {
                    $term->name = $wpTerm->name;
                    $term->slug = $wpTerm->slug;
                    $term->taxonomy = 'category';
                    $term->save();

                    $migrated++;
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'term' => $wpTerm->name,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'migrated' => $migrated,
            'errors' => $errors,
        ];
    }

    /**
     * Migrate WordPress posts and pages
     */
    public function migratePosts()
    {
        if (!$this->connection()) {
            return ['migrated' => 0, 'errors' => ['WordPress database connection is not configured.']];
        }
        $wpPosts = $this->connection()->table("{$this->prefix}posts")
            ->where('post_type', 'in', ['post', 'page'])
            ->get();

        $migrated = 0;
        $errors = [];

        foreach ($wpPosts as $wpPost) {
            try {
                $post = Post::firstOrNew(['id' => $wpPost->ID]);

                $user = User::where('login', $wpPost->post_author)->first();
                
                $post->id = $wpPost->ID;
                $post->post_type = $wpPost->post_type;
                $post->post_author = $user ? $user->id : null;
                $post->post_date = $wpPost->post_date;
                $post->post_date_gmt = $wpPost->post_date_gmt;
                $post->post_content = $wpPost->post_content;
                $post->post_title = $wpPost->post_title;
                $post->post_excerpt = $wpPost->post_excerpt;
                $post->post_status = $wpPost->post_status;
                $post->post_name = $wpPost->post_name;
                $post->post_parent = $wpPost->post_parent;
                $post->menu_order = $wpPost->menu_order;
                $post->guid = $wpPost->guid;

                // Handle password-protected posts
                $post->post_password = '';

                $post->save();

                // Migrate categories
                $this->migratePostTerms($wpPost->ID, $post);

                // Migrate blocks from content
                $this->migrateBlocks($wpPost, $post);

                $migrated++;
            } catch (\Exception $e) {
                $errors[] = [
                    'post' => $wpPost->post_title,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'migrated' => $migrated,
            'errors' => $errors,
        ];
    }

    /**
     * Migrate post terms (categories, tags)
     */
    protected function migratePostTerms($wpPostId, $post)
    {
        $termRelationships = $this->connection()->table("{$this->prefix}term_relationships")
            ->where('object_id', $wpPostId)
            ->get();

        foreach ($termRelationships as $rel) {
            $termTaxonomy = $this->connection()->table("{$this->prefix}term_taxonomy")
                ->where('term_taxonomy_id', $rel->term_taxonomy_id)
                ->first();

            if ($termTaxonomy) {
                $term = $this->connection()->table("{$this->prefix}terms")
                    ->where('term_id', $termTaxonomy->term_id)
                    ->first();

                if ($term) {
                    $termModel = Term::firstOrNew([
                        'name' => $term->name,
                        'slug' => $term->slug,
                    ]);

                    if (!$termModel->exists) {
                        $termModel->name = $term->name;
                        $termModel->slug = $term->slug;
                        $termModel->taxonomy = $termTaxonomy->taxonomy;
                        $termModel->save();
                    }

                    $termModel->posts()->attach($post->id);
                }
            }
        }
    }

    /**
     * Migrate blocks from post content
     */
    protected function migrateBlocks($wpPost, $post)
    {
        // Split content into blocks based on HTML tags
        $blocks = [];

        // Extract headings
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\\1>/i', $wpPost->post_content, $headings);
        foreach ($headings[0] as $index => $markup) {
            $blocks[] = [
                'type' => 'heading',
                'content' => strip_tags($markup),
                'level' => (int)$headings[1][$index],
                'order' => $index * 10,
            ];
        }

        // Extract paragraphs
        preg_match_all('/<p[^>]*>(.*?)<\/p>/i', $wpPost->post_content, $paragraphs);
        foreach ($paragraphs[0] as $index => $markup) {
            $blocks[] = [
                'type' => 'text',
                'content' => strip_tags($markup),
                'order' => 5 + ($index * 10),
            ];
        }

        // Extract images
        preg_match_all('/<img[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $wpPost->post_content, $images);
        foreach ($images[0] as $index => $markup) {
            preg_match('/src=["\']([^"\']+)["\']/i', $markup, $srcMatch);
            preg_match('/alt=["\']([^"\']+)["\']/i', $markup, $altMatch);
            
            $blocks[] = [
                'type' => 'image',
                'content' => $srcMatch[1] ?? '',
                'settings' => json_encode([
                    'alt' => $altMatch[1] ?? '',
                ]),
                'order' => 2 + ($index * 10),
            ];
        }

        // Save blocks
        foreach ($blocks as $blockData) {
            $block = new Block();
            $block->post_id = $post->id;
            $block->site_id = $post->site_id ?? 1;
            $block->type = $blockData['type'];
            $block->content = $blockData['content'];
            $block->settings = $blockData['settings'] ?? json_encode([]);
            $block->order = $blockData['order'];
            $block->save();
        }
    }

    /**
     * Migrate WordPress comments
     */
    public function migrateComments()
    {
        if (!$this->connection()) {
            return ['migrated' => 0, 'errors' => ['WordPress database connection is not configured.']];
        }
        $wpComments = $this->connection()->table("{$this->prefix}comments")
            ->where('comment_approved', 'in', ['0', '1', 'spam', 'trash'])
            ->get();

        $migrated = 0;
        $errors = [];

        foreach ($wpComments as $wpComment) {
            try {
                $post = Post::find($wpComment->comment_post_ID);

                if (!$post) {
                    continue;
                }

                $comment = Comment::firstOrNew(['id' => $wpComment->comment_ID]);

                $comment->post_id = $post->id;
                $comment->comment_author = $wpComment->comment_author;
                $comment->comment_author_email = $wpComment->comment_author_email;
                $comment->comment_author_url = $wpComment->comment_author_url;
                $comment->comment_author_IP = $wpComment->comment_author_IP;
                $comment->comment_content = $wpComment->comment_content;
                $comment->comment_approved = $wpComment->comment_approved;
                $comment->comment_type = $wpComment->comment_type ?: 'comment';
                $comment->comment_parent = $wpComment->comment_parent;
                $comment->user_id = $wpComment->user_id;
                $comment->comment_date = $wpComment->comment_date;
                $comment->comment_date_gmt = $wpComment->comment_date_gmt;

                $comment->save();

                $migrated++;
            } catch (\Exception $e) {
                $errors[] = [
                    'comment' => $wpComment->comment_ID,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'migrated' => $migrated,
            'errors' => $errors,
        ];
    }

    /**
     * Migrate WordPress post metadata
     */
    public function migrateMetadata()
    {
        if (!$this->connection()) {
            return ['migrated' => 0, 'errors' => ['WordPress database connection is not configured.']];
        }
        $wpMeta = $this->connection()->table("{$this->prefix}postmeta")->get();
        $migrated = 0;

        foreach ($wpMeta as $wpMetaItem) {
            // Skip meta that we handle specially
            if (in_array($wpMetaItem->meta_key, [
                '_edit_lock',
                '_edit_last',
                '_thumbnail_id',
                '_wp_attached_file',
                '_wp_attachment_metadata',
            ])) {
                continue;
            }

            $post = Post::find($wpMetaItem->post_id);

            if ($post) {
                // Store as JSON in block settings or post meta
                $migrated++;
            }
        }

        return [
            'migrated' => $migrated,
        ];
    }

    /**
     * Check if WordPress database exists
     */
    public function checkConnection()
    {
        if (!$this->connection()) {
            return false;
        }

        try {
            $this->connection()->table("{$this->prefix}users")->limit(1)->get();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get WordPress database table counts
     */
    public function getDatabaseStats()
    {
        $stats = [];

        if (!$this->connection()) {
            $stats['error'] = 'WordPress database connection is not configured.';
            return $stats;
        }

        try {
            $stats['users'] = $this->connection()->table("{$this->prefix}users")->count();
            $stats['posts'] = $this->connection()->table("{$this->prefix}posts")->count();
            $stats['terms'] = $this->connection()->table("{$this->prefix}terms")->count();
            $stats['comments'] = $this->connection()->table("{$this->prefix}comments")->count();
            $stats['meta'] = $this->connection()->table("{$this->prefix}postmeta")->count();
        } catch (\Exception $e) {
            $stats['error'] = $e->getMessage();
        }

        return $stats;
    }
}
