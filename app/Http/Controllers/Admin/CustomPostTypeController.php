<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SavesPostContent;
use App\Traits\SanitizeContent;
use App\Models\Category;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Support\PostTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Generic admin controller for the custom post types registered in
 * config/post_types.php (portfolio, testimonial, event, …).
 *
 * The "post" type keeps its dedicated PostController. Everything else is
 * served from here so each type gets a consistent list/form/show UI without
 * duplicating code.
 */
class CustomPostTypeController extends Controller
{
    use SanitizeContent, SavesPostContent;

    /**
     * Abort 403 when the current user lacks the given permission for the type.
     */
    protected function enforce(string $type, string $action): void
    {
        if (!auth()->user()->canPerform($type, $action)) {
            abort(403, 'You do not have permission to ' . $action . ' this content type.');
        }
    }

    /**
     * Resolve the type definition for the given slug, or 404.
     */
    protected function typeDef(string $type): array
    {
        $def = PostTypes::get($type);

        abort_unless($def && PostTypes::isGeneric($type), 404, 'Unknown post type.');

        return $def;
    }

    /**
     * Resolve a post belonging to the given type, or 404.
     */
    protected function findTypePost(string $type, int $id): Post
    {
        $post = Post::where('post_type', $type)->findOrFail($id);

        return $post;
    }

    public function index(Request $request, string $type)
    {
        $this->enforce($type, 'view');

        $def = $this->typeDef($type);
        $search = trim((string) $request->input('search'));

        $posts = Post::where('post_type', $type)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%');
                });
            })
            ->with('user', 'site')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.custom-posts.index', [
            'posts' => $posts,
            'type' => $type,
            'def' => $def,
            'search' => $search,
        ]);
    }

    public function create(string $type)
    {
        $this->enforce($type, 'create');

        if (!auth()->user()->canEditField($type, 'title')) {
            abort(403, 'You do not have permission to edit the title of this content type.');
        }

        $def = $this->typeDef($type);

        return view('admin.custom-posts.form', [
            'action' => 'Create',
            'post' => new Post(['post_type' => $type]),
            'postType' => $type,
            'def' => $def,
            'editableFields' => auth()->user()->editableFieldsFor($type),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $type)
    {
        $this->enforce($type, 'create');

        if (!auth()->user()->canEditField($type, 'title')) {
            abort(403, 'You do not have permission to edit the title of this content type.');
        }

        $def = $this->typeDef($type);

        $post = new Post(['post_type' => $type]);
        $post->user_id = auth()->id();

        $this->savePost($request, $post, $type);

        return redirect()->route('admin.custom-posts.edit', [$type, $post->id])
            ->with('success', $def['label'] . ' created successfully.');
    }

    public function edit(string $type, Post $post)
    {
        $this->enforce($type, 'edit');

        $def = $this->typeDef($type);
        $this->findTypePost($type, $post->id);

        return view('admin.custom-posts.form', [
            'action' => 'Edit',
            'post' => $post,
            'postType' => $type,
            'def' => $def,
            'editableFields' => auth()->user()->editableFieldsFor($type),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $type, Post $post)
    {
        $this->enforce($type, 'edit');

        $def = $this->typeDef($type);
        $this->findTypePost($type, $post->id);

        $lock = \App\Support\EditorLock::conflictingLock($post, auth()->user());
        if ($lock) {
            return redirect()->route('admin.custom-posts.edit', [$type, $post->id])
                ->withInput()
                ->with('error', 'This ' . strtolower($def['label']) . ' is being edited by ' . $lock->user->name . '. Your changes were not saved — please wait for them to finish, then try again.');
        }

        $this->savePost($request, $post, $type);

        return redirect()->route('admin.custom-posts.edit', [$type, $post->id])
            ->with('success', $def['label'] . ' updated successfully.');
    }

    public function destroy(string $type, Post $post)
    {
        $this->enforce($type, 'delete');

        $this->typeDef($type);
        $this->findTypePost($type, $post->id);

        $post->delete();

        return redirect()->route('admin.custom-posts.index', $type)
            ->with('success', 'Deleted successfully.');
    }
}
