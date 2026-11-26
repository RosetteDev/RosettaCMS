<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'key_prefix',
        'key_hash',
        'scopes',
        'last_used_at',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $hidden = [
        'key_hash',
    ];

    /**
     * Scopes that can be granted to an API key.
     */
    public const AVAILABLE_SCOPES = [
        'posts:read',
        'posts:write',
        'pages:read',
        'pages:write',
        'media:read',
        'media:write',
    ];

    /**
     * Generate a new API key for the given user.
     *
     * Returns an array with the plaintext key (shown once) and the model.
     */
    public static function createKey(int $userId, string $name, array $scopes = [], ?\DateTimeInterface $expiresAt = null): array
    {
        $plaintext = 'rk_' . Str::random(48);
        $prefix = substr($plaintext, 0, 12);

        $key = static::create([
            'user_id' => $userId,
            'name' => $name,
            'key_prefix' => $prefix,
            'key_hash' => bcrypt($plaintext),
            'scopes' => array_values($scopes),
            'expires_at' => $expiresAt,
        ]);

        return ['key' => $key, 'plaintext' => $plaintext];
    }

    /**
     * Find a valid (not revoked, not expired) key by its plaintext value.
     */
    public static function findValid(string $plaintext): ?self
    {
        if ($plaintext === '') {
            return null;
        }

        $prefix = substr($plaintext, 0, 12);

        return static::query()
            ->where('key_prefix', $prefix)
            ->whereNull('revoked_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get()
            ->first(fn (self $key) => Hash::check($plaintext, $key->key_hash));
    }

    /**
     * Check whether the key grants the given scope.
     */
    public function hasScope(string $scope): bool
    {
        $scopes = $this->scopes ?? [];

        return in_array($scope, $scopes, true) || in_array('*', $scopes, true);
    }

    /**
     * Check whether the key is currently usable.
     */
    public function isValid(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Record a use of the key.
     */
    public function touchUsage(): void
    {
        $this->forceFill(['last_used_at' => now()])->saveQuietly();
    }

    /**
     * Revoke the key.
     */
    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->saveQuietly();
    }

    /**
     * Resolve the user that owns the key.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
