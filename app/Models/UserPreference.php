<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_preferences';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'theme',
        'language',
        'timezone',
        'date_format',
        'time_format',
        'list_per_page',
        'dashboard_widgets',
        'editor_mode',
        'rich_editing',
        'show_admin_bar_front',
        'show_admin_bar_admin',
        'color_scheme',
        'settings',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get or create preference for a user.
     */
    public static function forUser(int $userId): self
    {
        return self::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Get a specific preference value.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Set a preference value.
     */
    public function set(string $key, mixed $value): self
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
        return $this;
    }

    /**
     * Check if user has dark mode enabled.
     */
    public function isDarkMode(): bool
    {
        return $this->theme === 'dark' || ($this->settings['admin_theme'] ?? null) === 'dark';
    }
}
