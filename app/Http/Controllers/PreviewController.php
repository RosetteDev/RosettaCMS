<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Post;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    /**
     * Display a post with live preview.
     *
     * The frontend preview renders the post's blocks exactly as the editor
     * left them: the editor pushes its full current state to the JSON
     * preview endpoint, which stores it in the session under
     * "preview.changes.{postId}". This route replays that payload before
     * rendering, so the frontend preview reflects the live editor state —
     * including unsaved new blocks and deletions — not just persisted blocks.
     */
    public function show($postId, Request $request)
    {
        $post = Post::findOrFail($postId);

        // Check if user has permission to preview
        if (!$request->user() || !$request->user()->can('view', $post)) {
            abort(403, 'Unauthorized');
        }

        // Load blocks for the post
        $post->load('blocks');

        // Replay the editor's stored live state (if any).
        $stored = $request->session()->get('preview.changes.' . $post->id);
        if (is_array($stored) && !empty($stored)) {
            $post->setRelation('blocks', $this->applyBlocksPayload($post, $stored));
        }

        // The page view expects a page model; the previewed post is the page.
        $page = $post;

        // Get preview parameters
        $theme = $request->input('theme', config('app.theme'));
        $layout = $request->input('layout', 'default');

        // Store preview data in session for real-time updates
        if ($request->has('changes')) {
            $changes = $request->json('changes');
            $request->session()->put('preview.changes.' . $postId, $changes);
        }

        return view('themes.rosetta.page', compact('post', 'page', 'theme', 'layout'));
    }

    /**
     * Preview post with changes.
     *
     * Two payload shapes are supported:
     *  - Legacy "changes": an array of {id, content} entries. Changes are
     *    merged into the persisted blocks and every block of the post is
     *    rendered (blocks not listed remain unchanged).
     *  - Full editor state: an array of {id?, type, content, settings, order}
     *    entries describing the complete current state of the editor,
     *    including unsaved new blocks. Blocks absent from the payload are
     *    treated as deleted. In-memory (unsaved) blocks are rendered
     *    standalone — they are never written to the database.
     *
     * A full-state payload is also stored in the session so the frontend
     * preview route renders the same live state.
     */
    public function preview(Post $post, Request $request)
    {
        // Check permission
        if (!$request->user() || !$request->user()->can('edit', $post)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Load blocks with latest changes
        $post->load('blocks');

        $payload = $request->json('blocks');

        if (is_array($payload) && !empty($payload)) {
            $isFullState = collect($payload)->contains(function ($change) {
                return is_array($change) && isset($change['type']);
            });

            $post->setRelation('blocks', $this->applyBlocksPayload($post, $payload));

            // Persist the full editor state for the frontend preview route.
            if ($isFullState) {
                $request->session()->put('preview.changes.' . $post->id, $payload);
            }
        }

        return response()->json([
            'success' => true,
            'html' => view('themes.rosetta.partials.blocks', compact('post'))->render(),
        ]);
    }

    /**
     * Real-time preview of theme changes.
     */
    public function themePreview(Request $request)
    {
        // Check if user has permission to preview
        if (!$request->user() || !$request->user()->can('edit', new Post())) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $theme = $request->input('theme', config('app.theme'));
        $themeData = $this->loadThemeData($theme);

        return response()->json([
            'success' => true,
            'theme' => $theme,
            'preview_url' => route('preview.show', ['post' => 1, 'theme' => $theme]),
            'styles' => $this->generateThemeStyles($themeData),
        ]);
    }

    /**
     * Exit theme preview.
     */
    public function exitThemePreview(Request $request)
    {
        $request->session()->forget('preview.theme');

        return response()->json(['success' => true]);
    }

    /**
     * Apply a preview payload to the post's blocks.
     *
     * Supports both payload shapes (legacy "changes" and full editor state)
     * and returns the merged/ordered collection of blocks.
     *
     * @param  array<int, array>  $payload
     * @return \Illuminate\Support\Collection<int, Block>
     */
    protected function applyBlocksPayload(Post $post, array $payload)
    {
        $isFullState = collect($payload)->contains(function ($change) {
            return is_array($change) && isset($change['type']);
        });

        if ($isFullState) {
            $merged = collect();

            foreach ($payload as $change) {
                if (!is_array($change)) {
                    continue;
                }

                $existing = !empty($change['id'])
                    ? $post->blocks->firstWhere('id', $change['id'])
                    : null;

                if ($existing) {
                    $existing->content = array_merge($existing->content ?? [], $change['content'] ?? []);
                    $existing->settings = $change['settings'] ?? $existing->settings;
                    $existing->order = $change['order'] ?? $existing->order;
                    $merged->push($existing);
                } else {
                    $merged->push(new Block([
                        'post_id' => $post->id,
                        'site_id' => $post->site_id ?? null,
                        'type' => $change['type'] ?? 'text',
                        'content' => $change['content'] ?? [],
                        'settings' => $change['settings'] ?? [],
                        'order' => $change['order'] ?? 0,
                        'parent_block_id' => $change['parent_block_id'] ?? null,
                    ]));
                }
            }

            return $merged->sortBy('order')->values();
        }

        // Legacy changes payload: merge into persisted blocks, keep order.
        $blocks = $post->blocks->map(function ($block) use ($payload) {
            foreach ($payload as $change) {
                if (is_array($change) && ($change['id'] ?? null) == $block->id && isset($change['content']) && is_array($change['content'])) {
                    $block->content = array_merge($block->content ?? [], $change['content']);
                }
            }

            return $block;
        })->values();

        return $blocks;
    }

    /**
     * Load theme data.
     */
    protected function loadThemeData(string $theme): array
    {
        // This would load theme configuration
        return ['theme' => $theme, 'version' => '1.0'];
    }

    /**
     * Generate theme styles.
     */
    protected function generateThemeStyles(array $themeData): string
    {
        // This would generate theme styles
        return '/* Theme styles */';
    }
}
