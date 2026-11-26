<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    /**
     * Display the languages list.
     */
    public function index()
    {
        $languages = Language::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.languages.index', compact('languages'));
    }

    /**
     * Show the form for creating a new language.
     */
    public function create()
    {
        return view('admin.languages.create');
    }

    /**
     * Store a newly created language.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:2', 'alpha', 'unique:languages,code'],
            'name' => 'required|string|max:100',
            'native_name' => 'nullable|string|max:100',
            'flag' => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $isDefault = (bool) ($validated['is_default'] ?? false);

        if ($isDefault) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $language = Language::create([
            'code' => strtolower($validated['code']),
            'name' => $validated['name'],
            'native_name' => $validated['native_name'] ?? null,
            'flag' => $validated['flag'] ?? null,
            'is_default' => $isDefault,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.languages.index')
            ->with('success', "Language \"{$language->name}\" created.");
    }

    /**
     * Show the edit form.
     */
    public function edit(Language $language)
    {
        return view('admin.languages.edit', compact('language'));
    }

    /**
     * Update an existing language.
     */
    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:2', 'alpha', Rule::unique('languages', 'code')->ignore($language->id)],
            'name' => 'required|string|max:100',
            'native_name' => 'nullable|string|max:100',
            'flag' => 'nullable|string|max:10',
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $isDefault = (bool) ($validated['is_default'] ?? false);

        if ($isDefault) {
            Language::where('is_default', true)->where('id', '!=', $language->id)->update(['is_default' => false]);
        }

        $language->update([
            'code' => strtolower($validated['code']),
            'name' => $validated['name'],
            'native_name' => $validated['native_name'] ?? null,
            'flag' => $validated['flag'] ?? null,
            'is_default' => $isDefault,
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.languages.index')
            ->with('success', "Language \"{$language->name}\" updated.");
    }

    /**
     * Toggle the active state of a language.
     */
    public function toggle(Language $language)
    {
        $language->update(['is_active' => !$language->is_active]);

        return redirect()->route('admin.languages.index')
            ->with('success', "Language \"{$language->name}\" " . ($language->is_active ? 'enabled' : 'disabled') . '.');
    }

    /**
     * Delete a language.
     */
    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return redirect()->route('admin.languages.index')
                ->with('error', 'The default language cannot be deleted.');
        }

        $name = $language->name;
        $language->translations()->delete();
        $language->delete();

        return redirect()->route('admin.languages.index')
            ->with('success', "Language \"{$name}\" deleted.");
    }
}
