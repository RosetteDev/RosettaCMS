<?php

use App\Models\Language;
use App\Models\TranslationString;

/**
 * Look up a reusable translated string for the active language.
 *
 * Returns the stored translation, or $fallback when no translation exists
 * (or no language is active).
 *
 * @param string      $key      The dot-separated string key, e.g. "nav.contact".
 * @param string|null $fallback Value to return when no translation exists.
 */
function reusable_string(string $key, ?string $fallback = null): ?string
{
    $language = request()->attributes->get('activeLanguage');

    if (!$language) {
        $language = Language::defaultLanguage();
    }

    return TranslationString::get($key, $language, $fallback);
}
