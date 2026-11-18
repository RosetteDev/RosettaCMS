<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Traits\SanitizeContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

class PostController extends Controller
{
    use SanitizeContent;

    /**
     * Display a listing of posts.
     */
    public function index()
    {
        $posts = Post::where('post_type', 'post')
            ->with('user', 'site')
            ->latest()
            ->paginate(20);

        $settings = SiteSetting::forSite();

        return view('admin.posts.index', compact('posts', 'settings'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        return view('admin.posts.form', [
            'action' => 'Create',
            'post' => new Post(),
            'postType' => 'post',
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $post = new Post();
        $post->user_id = auth()->id();

        $this->savePost($request, $post, 'post');

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post created successfully.');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        return view('admin.posts.form', [
            'action' => 'Edit',
            'post' => $post,
            'postType' => 'post',
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        $this->savePost($request, $post, 'post');

        return redirect()->route('admin.posts.edit', $post->id)
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Validate and persist a post/page from the shared editor.
     */
    protected function savePost(Request $request, Post $post, string $type = 'post'): Post
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($post->id),
            ],
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|string|max:2048',
            'blocks' => 'nullable|array',
            'status' => 'nullable|in:draft,pending,published,private',
            'published_at' => 'nullable|date',
            'comment_status' => 'nullable|in:open,closed',
            'site_id' => 'nullable|exists:sites,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',

            // SEO
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url|max:2048',
            'robots' => 'nullable|in:index,follow,noindex,nofollow',
            'og_image' => 'nullable|string|max:2048',
        ]);

        $content = $validated['content'] ?? null;
        if ($content) {
            $content = $this->sanitizeContent($content);
        }

        $status = $validated['status'] ?? 'draft';

        // Determine the publish timestamp.
        $publishedAt = $validated['published_at'] ?? null;
        if (!$publishedAt && $status === 'published' && !$post->published_at) {
            $publishedAt = now();
        } elseif ($post->published_at && !$publishedAt) {
            $publishedAt = $post->published_at;
        }

        // Build the SEO meta payload (drops empty values).
        $meta = array_filter([
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'focus_keyword' => $validated['focus_keyword'] ?? null,
            'canonical_url' => $validated['canonical_url'] ?? null,
            'robots' => $validated['robots'] ?? null,
            'og_image' => $validated['og_image'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        $post->fill([
            'site_id' => $validated['site_id'] ?? $post->site_id ?? optional(\App\Models\Site::first())->id,
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Str::slug($validated['title']),
            'content' => $content,
            'excerpt' => $validated['excerpt'] ?? null,
            'featured_image' => $validated['featured_image'] ?? null,
            'blocks' => $validated['blocks'] ?? null,
            'meta' => $meta ?: null,
            'post_type' => $type,
            'status' => $status,
            'published_at' => $publishedAt,
            'comment_status' => $validated['comment_status'] ?? 'open',
        ]);

        $post->save();

        // Taxonomies only apply to posts.
        if ($type === 'post') {
            $post->categories()->sync($validated['categories'] ?? []);
            $post->tags()->sync($validated['tags'] ?? []);
        }

        return $post;
    }
}
