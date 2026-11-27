<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'admin_theme',
        'show_welcome_tour',
        'dashboard_widgets',
        'dashboard_layout',
        'two_factor_enabled_at',
        'failed_login_attempts',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dashboard_widgets' => 'array',
            'dashboard_layout' => 'array',
            'two_factor_enabled_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Whether two-factor authentication is enabled for the user.
     */
    public function hasTwoFactor(): bool
    {
        return !empty($this->two_factor_secret) && !is_null($this->two_factor_enabled_at);
    }

    /**
     * Whether the user is currently locked out (brute-force protection).
     */
    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    /**
     * Record a failed login attempt, locking the account after 5 failures.
     */
    public function registerFailedLogin(int $threshold = 5, int $lockMinutes = 15): void
    {
        $this->failed_login_attempts = ($this->failed_login_attempts ?? 0) + 1;

        if ($this->failed_login_attempts >= $threshold) {
            $this->locked_until = now()->addMinutes($lockMinutes);
            $this->failed_login_attempts = 0;
        }

        $this->save();
    }

    /**
     * Reset the failed-login counter and any active lockout.
     */
    public function resetFailedLogins(): void
    {
        if (empty($this->failed_login_attempts) && is_null($this->locked_until)) {
            return;
        }

        $this->failed_login_attempts = 0;
        $this->locked_until = null;
        $this->save();
    }

    /**
     * Determine whether the user has any of the given role slugs.
     */
    public function hasRole(string ...$slugs): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->whereIn('slug', $slugs)->isNotEmpty();
        }

        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    /**
     * Determine whether the user has the given permission via any of their roles.
     */
    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $role) {
            $permissions = $role->permissions ?? [];
            if (!empty($permissions[$permission])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can reach the administration area.
     */
    public function isStaff(): bool
    {
        return $this->hasRole('admin', 'editor');
    }

    /**
     * Get the comments authored by this user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the API keys owned by this user.
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Scope a query to only include verified users.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope a query to only include pending users.
     */
    public function scopePending($query)
    {
        return $query->whereNull('email_verified_at');
    }
}
