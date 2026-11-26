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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
