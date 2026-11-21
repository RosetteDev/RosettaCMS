<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('posts')->orderBy('order')->orderBy('name')->get();
        
        $settings = SiteSetting::forSite();
        
        return view('admin.tags.index', compact('tags', 'settings'));
    }

    public function create()
    {
        return view('admin.tags.form', ['action' => 'Create']);
    }

    public function store(Request $request)
    {
        $siteId = \App\Models\Site::first()?->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('tags', 'slug')->where('site_id', $siteId),
            ],
            'description' => 'nullable|string',
            'color' => 'nullable|string|hex_color',
            'order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['site_id'] = $siteId;

        Tag::create($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully.');
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.form', ['action' => 'Edit', 'tag' => $tag]);
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('tags', 'slug')
                    ->where('site_id', $tag->site_id)
                    ->ignore($tag->id),
            ],
            'description' => 'nullable|string',
            'color' => 'nullable|string|hex_color',
            'order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $tag->update($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        // Check if tag has posts
        if ($tag->posts()->count() > 0) {
            return redirect()->route('admin.tags.index')
                ->with('error', 'Cannot delete tag with existing posts.');
        }

        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully.');
    }
}
