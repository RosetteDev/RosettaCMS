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
     */
    public function preview(Post $post, Request $request)
    {
        // Check permission
        if (!$request->user() || !$request->user()->can('edit', $post)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Load blocks with latest changes
        $post->load('blocks');

        // Apply any pending changes from request
        if ($request->has('blocks')) {
            $changes = $request->json('blocks');

            foreach ($changes as $change) {
                $block = $post->blocks->firstWhere('id', $change['id'] ?? null);

                if ($block && isset($change['content']) && is_array($change['content'])) {
                    $block->content = array_merge($block->content ?? [], $change['content']);
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
