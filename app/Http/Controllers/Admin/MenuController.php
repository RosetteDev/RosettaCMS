<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\LanguageContent;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    protected function site(): ?Site
    {
        return Site::first();
    }

    public function index()
    {
        $site = $this->site();
        $menus = $site
            ? Menu::where('site_id', $site->id)->withCount('items')->orderBy('name')->get()
            : collect();

        return view('admin.menus.index', compact('menus', 'site'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|in:header,footer,custom',
        ]);

        $site = $this->site();
        abort_unless($site, 500, 'No site configured.');

        Menu::create([
            'site_id' => $site->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'location' => $validated['location'],
        ]);

        return redirect()->route('admin.menus.index')->with('success', 'Menu created.');
    }

    public function edit(Menu $menu)
    {
        $menu->load(['rootItems.children']);
        $rootItems = $menu->rootItems()->with('children')->get();
        $allItems = MenuItem::where('menu_id', $menu->id)->orderBy('order')->get();
        $content = Post::whereIn('post_type', ['page', 'post'])->orderBy('title')->get();

        return view('admin.menus.edit', compact('menu', 'rootItems', 'allItems', 'content'));
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|in:header,footer,custom',
        ]);

        $menu->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'location' => $validated['location'],
        ]);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu updated.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu deleted.');
    }

    public function storeItem(Request $request, Menu $menu)
    {
        $validated = $this->validateItem($request);

        $item = $menu->items()->create($validated + ['order' => $validated['order'] ?? 0]);

        $this->saveTranslations($request, $item);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu item added.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);

        if ((int) $request->input('parent_id') === $item->id) {
            return back()->with('error', 'An item cannot be its own parent.');
        }

        $item->update($this->validateItem($request));

        $this->saveTranslations($request, $item);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu item updated.');
    }

    public function destroyItem(Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $item->delete();

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu item removed.');
    }

    /**
     * Persist translated label for each enabled (non-default) language.
     *
     * Empty translations remove the stored translation; a populated translation
     * upserts by (content_type, content_id, language).
     */
    protected function saveTranslations(Request $request, MenuItem $item): void
    {
        $translations = $request->input('translations', []);

        if (!is_array($translations)) {
            return;
        }

        foreach ($translations as $languageId => $fields) {
            $language = Language::find($languageId);

            if (!$language || !$language->is_active || $language->is_default) {
                continue;
            }

            $fields = is_array($fields) ? $fields : [];
            $title = trim((string) ($fields['title'] ?? ''));

            $existing = LanguageContent::where('content_type', 'menu_item')
                ->where('content_id', $item->id)
                ->where('language_id', $language->id)
                ->first();

            if ($title === '') {
                $existing?->delete();
                continue;
            }

            $payload = [
                'content_type' => 'menu_item',
                'content_id' => $item->id,
                'language_id' => $language->id,
                'title' => $title,
                'translated_by' => auth()->id(),
            ];

            if ($existing) {
                $existing->update($payload);
            } else {
                LanguageContent::create($payload);
            }
        }
    }

    /**
     * Validate a menu item payload.
     */
    protected function validateItem(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:page,post,custom',
            'page_id' => 'nullable|required_if:type,page,post|exists:posts,id',
            'url' => 'nullable|required_if:type,custom|string|max:2048',
            'target' => 'nullable|in:_self,_blank',
            'parent_id' => 'nullable|exists:menu_items,id',
            'order' => 'nullable|integer|min:0',
        ]);

        return [
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'page_id' => $validated['type'] === 'custom' ? null : ($validated['page_id'] ?? null),
            'url' => $validated['type'] === 'custom' ? ($validated['url'] ?? null) : null,
            'target' => $validated['target'] ?? '_self',
            'order' => $validated['order'] ?? 0,
        ];
    }

    /**
     * Reorder menu items.
     */
    public function reorder(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.order' => 'required|integer',
            'items.*.parent_id' => 'nullable|exists:menu_items,id',
        ]);

        foreach ($validated['items'] as $itemData) {
            MenuItem::where('id', $itemData['id'])
                ->where('menu_id', $menu->id)
                ->update([
                    'order' => $itemData['order'],
                    'parent_id' => $itemData['parent_id'] ?? null,
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Menu items reordered successfully.',
        ]);
    }
}
