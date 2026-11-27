<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    /**
     * Display a post with live preview.
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

        // The page view expects a page model; the previewed post is the page.
        $page = $post;

        // Get preview parameters
        $theme = $request->input('theme', config('app.theme'));
        $layout = $request->input('layout', 'default');

        // Store preview data in session for real-time updates
        if ($request->has('changes')) {
            $changes = $request->json('changes');
            $request->session()->put('preview.changes.'.$postId, $changes);
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
                        $merged->push(new \App\Models\Block([
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

                $post->setRelation('blocks', $merged->sortBy('order')->values());
            } else {
                foreach ($payload as $change) {
                    $block = $post->blocks->firstWhere('id', $change['id'] ?? null);

                    if ($block && isset($change['content']) && is_array($change['content'])) {
                        $block->content = array_merge($block->content ?? [], $change['content']);
                    }
                }
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
        $request->validate([
            'theme' => 'required|string',
            'settings' => 'nullable|array',
        ]);

        // Store theme preview in session
        $request->session()->put('preview.theme', $request->theme);
        $request->session()->put('preview.settings', $request->settings ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Theme preview started.',
        ]);
    }

    /**
     * Exit theme preview mode.
     */
    public function exitThemePreview(Request $request)
    {
        $request->session()->forget('preview.theme');
        $request->session()->forget('preview.settings');

        return response()->json([
            'success' => true,
            'message' => 'Preview mode exited.',
        ]);
    }
}
