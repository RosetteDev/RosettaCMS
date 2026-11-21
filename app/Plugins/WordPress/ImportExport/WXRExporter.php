<?php

namespace RosettaCMS\Plugins\WordPress\ImportExport;

use RosettaCMS\Models\Post;
use RosettaCMS\Models\Term;
use RosettaCMS\Models\User;
use RosettaCMS\Models\Comment;
use DOMDocument;
use DateTime;

class WXRExporter
{
    protected $exportsDir;
    protected $wxr;

    public function __construct()
    {
        $this->exportsDir = storage_path('app/wordpress-exports');
        if (!file_exists($this->exportsDir)) {
            mkdir($this->exportsDir, 0755, true);
        }
    }

    /**
     * Export all content to WXR format
     */
    public function exportAll()
    {
        $this->wxr = new DOMDocument('1.0', 'utf-8');
        $this->wxr->formatOutput = true;

        // Create root wxr element
        $wxr = $this->wxr->createElement('rss');
        $wxr->setAttribute('version', '2.2');
        $wxr->setAttribute('xmlns:excerpt', 'http://wordpress.org/export/1.2/excerpt/');
        $wxr->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $wxr->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $wxr->setAttribute('xmlns:wp', 'http://wordpress.org/export/1.2/');

        $wxr->appendChild($this->createChannel());
        $this->wxr->appendChild($wxr);

        $filename = 'rosetta-wordpress-export-' . date('Y-m-d') . '.xml';
        $filepath = $this->exportsDir . '/' . $filename;

        file_put_contents($filepath, $this->wxr->saveXML());

        return $filepath;
    }

    /**
     * Export specific post types
     */
    public function exportPostTypes($postTypes = ['post', 'page'])
    {
        $this->wxr = new DOMDocument('1.0', 'utf-8');
        $this->wxr->formatOutput = true;

        $wxr = $this->wxr->createElement('rss');
        $wxr->setAttribute('version', '2.2');
        $wxr->setAttribute('xmlns:excerpt', 'http://wordpress.org/export/1.2/excerpt/');
        $wxr->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
        $wxr->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $wxr->setAttribute('xmlns:wp', 'http://wordpress.org/export/1.2/');

        $channel = $this->createChannel();
        $wxr->appendChild($channel);

        // Export posts
        foreach ($postTypes as $postType) {
            $posts = Post::where('post_type', $postType)->get();
            foreach ($posts as $post) {
                $channel->appendChild($this->createItem($post));
            }
        }

        $this->wxr->appendChild($wxr);

        $filename = 'rosetta-wordpress-export-' . date('Y-m-d') . '-' . implode('-', $postTypes) . '.xml';
        $filepath = $this->exportsDir . '/' . $filename;

        file_put_contents($filepath, $this->wxr->saveXML());

        return $filepath;
    }

    /**
     * Create the RSS channel
     */
    protected function createChannel()
    {
        $channel = $this->wxr->createElement('channel');

        $channel->appendChild($this->wxr->createElement('title', config('app.name')));
        $channel->appendChild($this->wxr->createElement('link', config('app.url')));
        $channel->appendChild($this->wxr->createElement('description', config('app.name') . ' WordPress Export'));
        $channel->appendChild($this->wxr->createElement('wp:wxr_version', '1.2'));
        $channel->appendChild($this->wxr->createElement('wp:base_url', config('app.url')));
        $channel->appendChild($this->wxr->createElement('wp:base_blog_url', config('app.url')));

        // Create authors
        $authors = User::distinct('display_name')->get();
        foreach ($authors as $author) {
            $author_node = $this->wxr->createElement('wp:author');
            $author_node->appendChild($this->wxr->createElement('wp:author_login', $author->login));
            $author_node->appendChild($this->wxr->createElement('wp:author_display_name', $author->display_name));
            $author_node->appendChild($this->wxr->createElement('wp:author_email', $author->email));
            $author_node->appendChild($this->wxr->createElement('wp:author_first_name', ''));
            $author_node->appendChild($this->wxr->createElement('wp:author_last_name', ''));
            $channel->appendChild($author_node);
        }

        return $channel;
    }

    /**
     * Create a post item
     */
    protected function createItem(Post $post)
    {
        $item = $this->wxr->createElement('item');

        $item->appendChild($this->wxr->createElement('title', $post->post_title));
        $item->appendChild($this->wxr->createElement('link', $post->guid ?? $post->permalink()));
        $item->appendChild($this->wxr->createElement('pubDate', $this->formatDate($post->post_date)));
        $item->appendChild($this->wxr->createElement('dc:creator', $post->author ? $post->author->login : 'admin'));
        $item->appendChild($this->wxr->createElement('guid', $post->guid ?? $post->permalink()));
        $item->appendChild($this->wxr->createElement('description', ''));
        $item->appendChild($this->wxr->createElement('content:encoded', $this->wxr->createCDATASection($post->post_content)));
        $item->appendChild($this->wxr->createElement('excerpt:encoded', $this->wxr->createCDATASection($post->post_excerpt)));

        // WP specific elements
        $item->appendChild($this->wxr->createElement('wp:post_id', $post->id));
        $item->appendChild($this->wxr->createElement('wp:post_date', $this->formatDate($post->post_date)));
        $item->appendChild($this->wxr->createElement('wp:post_date_gmt', $this->formatDate($post->post_date_gmt ?? $post->post_date)));
        $item->appendChild($this->wxr->createElement('wp:post_content', $this->wxr->createCDATASection($post->post_content)));
        $item->appendChild($this->wxr->createElement('wp:post_title', $post->post_title));
        $item->appendChild($this->wxr->createElement('wp:post_excerpt', $post->post_excerpt));
        $item->appendChild($this->wxr->createElement('wp:post_status', $post->post_status));
        $item->appendChild($this->wxr->createElement('wp:post_name', $post->post_name));
        $item->appendChild($this->wxr->createElement('wp:post_type', $post->post_type));
        $item->appendChild($this->wxr->createElement('wp:post_password', ''));
        $item->appendChild($this->wxr->createElement('wp:is_sticky', $post->is_sticky ? '1' : '0'));
        $item->appendChild($this->wxr->createElement('wp:post_parent', $post->post_parent ?? '0'));
        $item->appendChild($this->wxr->createElement('wp:menu_order', $post->menu_order ?? '0'));
        $item->appendChild($this->wxr->createElement('wp:post_format', 'standard'));
        $item->appendChild($this->wxr->createElement('wp:post_metadata', 'a:0:{}'));

        // Taxonomies
        $terms = $post->terms ?? collect();
        foreach ($terms as $term) {
            $category_node = $this->wxr->createElement('category');
            $category_node->setAttribute('nicename', $term->slug);
            $category_node->setAttribute('domain', $term->taxonomy);
            if ($term->parent) {
                $category_node->setAttribute('parent', $term->parent->slug ?? '');
            }
            $category_node->appendChild($this->wxr->createElement('cat_name', $term->name));
            $item->appendChild($category_node);
        }

        // Comments
        $comments = $post->comments ?? collect();
        foreach ($comments as $comment) {
            $comment_node = $this->wxr->createElement('wp:comment');
            $comment_node->appendChild($this->wxr->createElement('wp:comment_id', $comment->id));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_author', $comment->comment_author));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_author_email', $comment->comment_author_email));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_author_url', $comment->comment_author_url));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_author_IP', '127.0.0.1'));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_date', $this->formatDate($comment->comment_date)));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_date_gmt', $this->formatDate($comment->comment_date_gmt ?? $comment->comment_date)));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_content', $comment->comment_content));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_approved', $comment->comment_approved));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_type', $comment->comment_type ?? 'comment'));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_parent', $comment->comment_parent ?? '0'));
            $comment_node->appendChild($this->wxr->createElement('wp:comment_meta', 'a:0:{}'));
            $item->appendChild($comment_node);
        }

        return $item;
    }

    /**
     * Format date for WXR
     */
    protected function formatDate($date)
    {
        if (!$date) {
            return date('D, d M Y H:i:s +0000');
        }

        try {
            $dt = new DateTime($date);
            return $dt->format('D, d M Y H:i:s +0000');
        } catch (\Exception $e) {
            return date('D, d M Y H:i:s +0000');
        }
    }

    /**
     * Get list of exported files
     */
    public function getExportedFiles()
    {
        $files = [];
        if (file_exists($this->exportsDir)) {
            foreach (glob($this->exportsDir . '/*.xml') as $file) {
                $files[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'modified' => filemtime($file),
                ];
            }
        }

        return $files;
    }

    /**
     * Delete specific export file
     */
    public function deleteExport($filename)
    {
        $filepath = $this->exportsDir . '/' . $filename;
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Download export file
     */
    public function downloadExport($filename)
    {
        $filepath = $this->exportsDir . '/' . $filename;
        if (file_exists($filepath)) {
            return response()->download($filepath);
        }
        abort(404, 'Export file not found');
    }
}
