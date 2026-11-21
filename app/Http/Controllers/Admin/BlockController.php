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
     */
    public function index(Post $post)
    {
        $blocks = Block::forPost($post->id)->with('childBlocks')->get();
        $availableBlocks = $this->visualEditorService->getAvailableBlocks();

        return view('admin.blocks.index', compact('post', 'blocks', 'availableBlocks'));
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
        $validated['order'] = $validated['order'] ?? Block::where('post_id', $post->id)->max('order') + 1;

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
            'order' => 'required|array',
            'order.*' => 'required|exists:blocks,id',
        ]);

        // Reorder all blocks for the post
        foreach ($validated['order'] as $index => $blockId) {
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
     * Get media picker data.
     */
    public function mediaPicker()
    {
        $media = \App\Models\Media::orderBy('created_at', 'desc')->take(50)->get(['id', 'filename', 'path', 'alt', 'title']);

        return response()->json([
            'success' => true,
            'media' => $media->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->title ?? $item->filename,
                    'alt' => $item->alt ?? '',
                    'url' => $item->url,
                    'path' => $item->path,
                ];
            })->toArray(),
        ]);
    }
}
