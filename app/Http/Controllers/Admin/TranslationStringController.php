<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\TranslationString;
use Illuminate\Http\Request;

class TranslationStringController extends Controller
{
    /**
     * Display the translation memory: all reusable strings, grouped by language.
     */
    public function index()
    {
        $languages = Language::where('is_active', true)->orderBy('sort_order')->get();

        $strings = TranslationString::with('language')->orderBy('key')->get();

        $byLanguage = $strings->groupBy('language_id');

        return view('admin.translations.index', compact('languages', 'byLanguage'));
    }

    /**
     * Store a new reusable translated string.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'language_id' => 'required|exists:languages,id',
            'value' => 'required|string|max:500',
        ]);

        $language = Language::findOrFail($validated['language_id']);

        if (!$language->is_active) {
            return back()->withErrors(['language_id' => 'The selected language is not active.']);
        }

        $key = trim($validated['key']);

        TranslationString::updateOrCreate(
            ['key' => $key, 'language_id' => $language->id],
            ['value' => trim($validated['value'])]
        );

        return redirect()->route('admin.translations.index')->with('success', 'Translation saved.');
    }

    /**
     * Update an existing reusable translated string.
     */
    public function update(Request $request, TranslationString $string)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'required|string|max:500',
        ]);

        $string->update([
            'key' => trim($validated['key']),
            'value' => trim($validated['value']),
        ]);

        return redirect()->route('admin.translations.index')->with('success', 'Translation updated.');
    }

    /**
     * Delete a reusable translated string.
     */
    public function destroy(TranslationString $string)
    {
        $string->delete();

        return redirect()->route('admin.translations.index')->with('success', 'Translation deleted.');
    }
}
