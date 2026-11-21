<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class InlineEditController extends Controller
{
    /**
     * Display a page with inline editing enabled.
     */
    public function show(Post $post, Request $request)
    {
        // Check if user has permission to edit
        if (!$request->user() || !$request->user()->can('edit', $post)) {
            abort(403, 'Unauthorized');
        }

        // Load blocks for the post
        $post->load('blocks');

        // Return the page with inline editing enabled
        return view('themes.rosetta.page', compact('post', 'request'));
    }

    /**
     * Update a block's attributes.
     */
    public function updateBlock(Request $request, Post $post, $blockId)
    {
        // Check permission
        if (!$request->user() || !$request->user()->can('edit', $post)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'attributes' => 'required|array',
        ]);

        $block = $post->blocks()->find($blockId);
        
        if (!$block) {
            return response()->json(['error' => 'Block not found'], 404);
        }

        $block->update([
            'attributes' => $validated['attributes'],
        ]);

        return response()->json([
            'success' => true,
            'block' => $block,
        ]);
    }

    /**
     * Delete a block.
     */
    public function destroyBlock(Request $request, Post $post, $blockId)
    {
        // Check permission
        if (!$request->user() || !$request->user()->can('edit', $post)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $block = $post->blocks()->find($blockId);
        
        if (!$block) {
            return response()->json(['error' => 'Block not found'], 404);
        }

        // Delete child blocks recursively
        $this->deleteBlockAndChildren($block);

        return response()->json([
            'success' => true,
            'message' => 'Block deleted successfully.',
        ]);
    }

    /**
     * Delete a block and all its children recursively.
     */
    protected function deleteBlockAndChildren($block)
    {
        $children = $block->childBlocks;
        
        foreach ($children as $child) {
            $this->deleteBlockAndChildren($child);
        }

        $block->delete();
    }
}
