<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * RTL languages (ISO 639-1 codes).
     */
    public const RTL_CODES = ['ar', 'he', 'fa', 'ur', 'ps', 'yi', 'dv', 'ckb'];

    /**
     * Determine whether this language is read right-to-left.
     */
    public function isRtl(): bool
    {
        return in_array($this->code, self::RTL_CODES, true);
    }

    /**
     * Get the translated content blocks for this language.
     */
    public function translations()
    {
        return $this->hasMany(LanguageContent::class);
    }

    /**
     * Look up the translation for a piece of content in this language.
     */
    public function translationFor(string $contentType, int $contentId): ?LanguageContent
    {
        return LanguageContent::where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('language_id', $this->id)
            ->first();
    }

    /**
     * The default (site) language.
     */
    public static function defaultLanguage(): ?self
    {
        return static::where('is_default', true)->first()
            ?? static::where('is_active', true)->orderBy('sort_order')->first();
    }
}
