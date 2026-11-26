<?php

namespace RosettaCMS\Plugins\WordPress\ImportExport;

use RosettaCMS\Models\Post;
use RosettaCMS\Models\Term;
use RosettaCMS\Models\User;
use RosettaCMS\Models\Comment;
use RosettaCMS\Models\TermRelationship;
use DOMDocument;
use Illuminate\Support\Str;

class WXRImporter
{
    protected $importDir;
    protected $file;
    protected $xml;

    public function __construct()
    {
        $this->importDir = storage_path('app/wordpress-imports');
        if (!file_exists($this->importDir)) {
            mkdir($this->importDir, 0755, true);
        }
    }

    /**
     * Import WXR file
     */
    public function import($filepath)
    {
        if (!file_exists($filepath)) {
            throw new \Exception('Import file not found: ' . $filepath);
        }

        $this->file = $filepath;

        // Load XML
        $this->xml = new DOMDocument();
        if (!$this->xml->load($filepath)) {
            throw new \Exception('Failed to load XML file');
        }

        $xpath = new \DOMXPath($this->xml);
        $xpath->registerNamespace('wp', 'http://wordpress.org/export/1.2/');

        $results = [];
        $errors = [];

        // Import authors
        $authors = $xpath->query('//wp:author');
        foreach ($authors as $author) {
            $results[] = $this->importAuthor($author);
        }

        // Import categories and tags
        $categories = $xpath->query('//wp:category');
        foreach ($categories as $category) {
            $results[] = $this->importCategory($category);
        }

        // Import posts
        $items = $this->xml->getElementsByTagName('item');
        foreach ($items as $item) {
            try {
                $result = $this->importItem($item);
                $results[] = $result;
            } catch (\Exception $e) {
                $errors[] = [
                    'item' => $item->getElementsByTagName('title')->item(0)->textContent ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => count($errors) === 0,
            'results' => $results,
            'errors' => $errors,
        ];
    }

    /**
     * Import author
     */
    protected function importAuthor($author)
    {
        $login = $this->getText($author, 'wp:author_login');
        $displayName = $this->getText($author, 'wp:author_display_name');
        $email = $this->getText($author, 'wp:author_email');

        $user = User::firstOrNew(['login' => $login]);

        if (!$user->exists) {
            $user->display_name = $displayName;
            $user->email = $email;
            // Generate a strong temporary password and surface it once in the import report.
            $tempPassword = Str::random(24);
            $user->password = bcrypt($tempPassword);
            $user->save();

            return [
                'type' => 'author',
                'login' => $login,
                'action' => 'created',
                'temp_password' => $tempPassword,
            ];
        }

        return [
            'type' => 'author',
            'login' => $login,
            'action' => $user->wasRecentlyCreated ? 'created' : 'found',
        ];
    }

    /**
     * Import category
     */
    protected function importCategory($category)
    {
        $slug = $category->getAttribute('nicename');
        $name = $this->getText($category, 'cat_name');
        $parent = $category->getAttribute('parent');

        $term = Term::firstOrNew(['slug' => $slug, 'taxonomy' => 'category']);

        if (!$term->exists) {
            $term->name = $name;
            $term->taxonomy = 'category';
            $term->save();
        }

        return [
            'type' => 'category',
            'slug' => $slug,
            'action' => $term->wasRecentlyCreated ? 'created' : 'found',
        ];
    }

    /**
     * Import post item
     */
    protected function importItem($item)
    {
        $postId = (int)$this->getText($item, 'wp:post_id');
        $postType = $this->getText($item, 'wp:post_type');
        $postTitle = $this->getText($item, 'title');
        $postContent = $this->getText($item, 'content:encoded');
        $postExcerpt = $this->getText($item, 'excerpt:encoded');
        $postStatus = $this->getText($item, 'wp:post_status');
        $postName = $this->getText($item, 'wp:post_name');
        $postDate = $this->getText($item, 'wp:post_date');
        $postDateGmt = $this->getText($item, 'wp:post_date_gmt');
        $postParent = (int)$this->getText($item, 'wp:post_parent');
        $menuOrder = (int)$this->getText($item, 'wp:menu_order');

        // Find or create post
        $post = Post::firstOrNew(['id' => $postId]);

        $post->post_type = $postType;
        $post->post_title = $postTitle;
        $post->post_content = $postContent;
        $post->post_excerpt = $postExcerpt;
        $post->post_status = $postStatus;
        $post->post_name = $postName;
        $post->post_date = $postDate;
        $post->post_date_gmt = $postDateGmt;
        $post->post_parent = $postParent;
        $post->menu_order = $menuOrder;

        // Set author
        $authorLogin = $this->getText($item, 'dc:creator');
        if ($authorLogin) {
            $author = User::where('login', $authorLogin)->first();
            if ($author) {
                $post->author_id = $author->id;
            }
        }

        $post->save();

        // Import categories
        $categories = $item->getElementsByTagName('category');
        foreach ($categories as $category) {
            $slug = $category->getAttribute('nicename');
            $taxonomy = $category->getAttribute('domain');

            if ($taxonomy === 'category' || $taxonomy === 'post_tag') {
                $term = Term::firstOrNew(['slug' => $slug, 'taxonomy' => $taxonomy]);
                if (!$term->exists) {
                    $term->name = $category->getElementsByTagName('cat_name')->item(0)->textContent ?? $slug;
                    $term->taxonomy = $taxonomy;
                    $term->save();
                }

                // Create relationship
                $term->posts()->attach($post->id);
            }
        }

        // Import comments
        $commentNodes = $item->getElementsByTagName('comment');
        foreach ($commentNodes as $commentNode) {
            $this->importComment($commentNode, $post);
        }

        return [
            'type' => 'post',
            'id' => $postId,
            'title' => $postTitle,
            'action' => $post->wasRecentlyCreated ? 'created' : 'updated',
        ];
    }

    /**
     * Import comment
     */
    protected function importComment($commentNode, $post)
    {
        $commentId = (int)$this->getText($commentNode, 'wp:comment_id');
        $commentAuthor = $this->getText($commentNode, 'wp:comment_author');
        $commentAuthorEmail = $this->getText($commentNode, 'wp:comment_author_email');
        $commentAuthorUrl = $this->getText($commentNode, 'wp:comment_author_url');
        $commentContent = $this->getText($commentNode, 'wp:comment_content');
        $commentApproved = $this->getText($commentNode, 'wp:comment_approved');
        $commentDate = $this->getText($commentNode, 'wp:comment_date');
        $commentParent = (int)$this->getText($commentNode, 'wp:comment_parent');

        $comment = Comment::firstOrNew(['id' => $commentId]);

        $comment->post_id = $post->id;
        $comment->comment_author = $commentAuthor;
        $comment->comment_author_email = $commentAuthorEmail;
        $comment->comment_author_url = $commentAuthorUrl;
        $comment->comment_content = $commentContent;
        $comment->comment_approved = $commentApproved;
        $comment->comment_date = $commentDate;
        $comment->comment_parent = $commentParent;

        $comment->save();

        return [
            'type' => 'comment',
            'id' => $commentId,
            'action' => $comment->wasRecentlyCreated ? 'created' : 'updated',
        ];
    }

    /**
     * Get text content from XML element
     */
    protected function getText($element, $tagName)
    {
        $nodes = $element->getElementsByTagName($tagName);
        if ($nodes->length > 0) {
            return $nodes->item(0)->textContent;
        }
        return '';
    }

    /**
     * Handle file upload for import
     */
    public function handleUpload($file)
    {
        $filename = 'import-' . time() . '-' . basename($file->getClientOriginalName());
        $filepath = $this->importDir . '/' . $filename;

        $file->move($this->importDir, $filename);

        return $filepath;
    }

    /**
     * Get list of import files
     */
    public function getImportedFiles()
    {
        $files = [];
        if (file_exists($this->importDir)) {
            foreach (glob($this->importDir . '/*.xml') as $file) {
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
}
