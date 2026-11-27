<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TranslationString extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'language_id',
        'value',
    ];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Look up a reusable translated string for a key and language.
     *
     * Returns null when no translation exists so callers can fall back
     * to a default.
     */
    public static function get(string $key, ?Language $language = null, ?string $fallback = null): ?string
    {
        $language = $language ?: Language::defaultLanguage();

        if (!$language) {
            return $fallback;
        }

        $row = static::where('key', $key)
            ->where('language_id', $language->id)
            ->first();

        return $row?->value ?? $fallback;
    }
}
