<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReusableBlock;
use App\Models\Site;
use Illuminate\Http\Request;

class ReusableBlockController extends Controller
{
    /**
     * Display a listing of reusable blocks.
     */
    public function index()
    {
        $reusableBlocks = ReusableBlock::with('site')->latest()->get();

        return view('admin.reusable-blocks.index', compact('reusableBlocks'));
    }

    /**
     * Create a reusable block from a set of root blocks.
     *
     * Only single-level root blocks are accepted: the page editor operates on
     * top-level blocks only, and container blocks with nested children cannot
     * be faithfully stored in a template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'blocks' => 'required|array|min:1|max:10',
            'blocks.*.type' => 'required|string|max:50',
            'blocks.*.content' => 'nullable|array',
            'blocks.*.settings' => 'nullable|array',
        ]);

        $blocks = collect($validated['blocks'])->map(function ($block) {
            return [
                'type' => $block['type'],
                'content' => $block['content'] ?? [],
                'settings' => $block['settings'] ?? [],
            ];
        })->values()->all();

        $reusableBlock = ReusableBlock::create([
            'site_id' => optional(Site::first())->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'payload' => $blocks,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'reusableBlock' => [
                    'id' => $reusableBlock->id,
                    'name' => $reusableBlock->name,
                    'description' => $reusableBlock->description,
                    'blocks' => $reusableBlock->blocks(),
                ],
            ], 201);
        }

        return redirect()->route('admin.reusable-blocks.index')->with('success', 'Reusable block saved.');
    }

    /**
     * Delete a reusable block.
     */
    public function destroy(ReusableBlock $reusableBlock)
    {
        $reusableBlock->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.reusable-blocks.index')->with('success', 'Reusable block deleted.');
    }
}
