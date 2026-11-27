<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Post;
use Illuminate\Http\Request;
use RosettaCMS\Plugins\VisualEditor\VisualEditorService;

class BlockController extends Controller
{
    protected $visualEditorService;

    public function __construct(VisualEditorService $visualEditorService)
    {
        $this->visualEditorService = $visualEditorService;
    }

    /**
     * Display a listing of blocks for a post.
     *
     * The page builder is embedded in the page form, so send users there.
     */
    public function index(Post $post)
    {
        return redirect()->route('admin.pages.edit', $post);
    }

    /**
     * Store a newly created block.
     */
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'parent_id' => 'nullable|exists:blocks,id',
            'order' => 'nullable|integer',
            'content' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $validated['post_id'] = $post->id;
        $validated['site_id'] = $post->site_id ?? optional(\App\Models\Site::first())->id;
        $validated['parent_block_id'] = $validated['parent_id'] ?? null;
        unset($validated['parent_id']);
        $validated['order'] = $validated['order'] ?? (Block::where('post_id', $post->id)->max('order') + 1);

        $block = Block::create($validated);

        return response()->json([
            'success' => true,
            'block' => $block->load('childBlocks'),
        ]);
    }

    /**
     * Display the specified block.
     */
    public function show(Post $post, Block $block)
    {
        abort_unless($block->post_id === $post->id, 404);

        $block->load('childBlocks');

        return response()->json($block);
    }

    /**
     * Update the specified block.
     */
    public function update(Request $request, Post $post, Block $block)
    {
        abort_unless($block->post_id === $post->id, 404);

        $validated = $request->validate([
            'type' => 'sometimes|string|max:50',
            'parent_id' => 'nullable|exists:blocks,id',
            'order' => 'nullable|integer',
            'content' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        if (array_key_exists('parent_id', $validated)) {
            $validated['parent_block_id'] = $validated['parent_id'];
            unset($validated['parent_id']);
        }

        $block->update($validated);

        return response()->json([
            'success' => true,
            'block' => $block->load('childBlocks'),
        ]);
    }

    /**
     * Remove the specified block.
     */
    public function destroy(Post $post, Block $block)
    {
        abort_unless($block->post_id === $post->id, 404);

        // Delete child blocks recursively
        $this->deleteBlockAndChildren($block);

        return response()->json([
            'success' => true,
            'message' => 'Block deleted successfully.',
        ]);
    }

    /**
     * Reorder blocks.
     */
    public function reorder(Request $request, Post $post)
    {
        $validated = $request->validate([
            'order' => 'nullable|array',
            'order.*' => 'required|exists:blocks,id',
        ]);

        // Reorder all blocks for the post
        foreach ($validated['order'] ?? [] as $index => $blockId) {
            Block::where('id', $blockId)
                ->where('post_id', $post->id)
                ->update(['order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Blocks reordered successfully.',
        ]);
    }

    /**
     * Get block configuration.
     */
    public function config()
    {
        return response()->json($this->visualEditorService->getConfig());
    }

    /**
     * Delete a block and all its children recursively.
     */
    protected function deleteBlockAndChildren(Block $block)
    {
        $children = Block::where('parent_block_id', $block->id)->get();

        foreach ($children as $child) {
            $this->deleteBlockAndChildren($child);
        }

        $block->delete();
    }

    /**
     * Get available block types.
     */
    public function blockTypes()
    {
        $availableBlocks = $this->visualEditorService->getAvailableBlocks();
        
        return response()->json([
            'success' => true,
            'blocks' => $availableBlocks,
        ]);
    }

    /**
     * Get media picker data (images only).
     */
    public function mediaPicker()
    {
        $media = \App\Models\Media::where('mime_type', 'like', 'image/%')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $items = $media->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->caption ?? $item->file_name,
                'alt' => $item->alt_text ?? '',
                'url' => $item->url,
                'path' => $item->file_path,
            ];
        })->values()->all();

        return response()->json($items);
    }
}
