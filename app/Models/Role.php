<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
        'post_type_permissions',
        'field_permissions',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'post_type_permissions' => 'array',
            'field_permissions' => 'array',
        ];
    }

    /**
     * Get the users that belong to this role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Every post type the CMS knows about (post, page, portfolio, …).
     */
    public static function postTypes(): array
    {
        return array_merge(['page'], array_keys(\App\Support\PostTypes::all()));
    }

    /**
     * Fields a role may edit on a post, keyed by slug.
     */
    public static function editableFields(): array
    {
        return [
            'title' => 'Title',
            'slug' => 'Slug',
            'content' => 'Content',
            'excerpt' => 'Excerpt',
            'featured_image' => 'Featured image',
            'seo_title' => 'SEO title',
            'seo_description' => 'Meta description',
            'custom_fields' => 'Custom fields',
            'status' => 'Status',
            'published_at' => 'Publish date',
            'translations' => 'Translations',
        ];
    }

    /**
     * Determine whether the role can perform an action on a post type.
     *
     * Admins bypass everything (handled by the caller); for everyone else,
     * an explicit per-post-type permission map wins. When a type is not
     * listed, we fall back to the legacy flat permission so existing roles
     * keep working.
     */
    public function canPerform(string $type, string $action): bool
    {
        $map = $this->post_type_permissions ?? [];

        if (array_key_exists($type, $map)) {
            return (bool) ($map[$type][$action] ?? false);
        }

        // Fallback: legacy flat permissions.
        $flat = $this->permissions ?? [];
        if ($type === 'page') {
            return !empty($flat['pages']);
        }

        return !empty($flat['posts']);
    }

    /**
     * Determine whether the role may edit a single field on a post type.
     */
    public function canEditField(string $type, string $field): bool
    {
        $map = $this->field_permissions ?? [];

        if (array_key_exists($type, $map)) {
            return in_array($field, $map[$type] ?? [], true);
        }

        // Fallback: if the role can edit the type, all fields are editable.
        return $this->canPerform($type, 'edit');
    }

    /**
     * List the fields this role may edit for a post type.
     */
    public function editableFieldsFor(string $type): array
    {
        $map = $this->field_permissions ?? [];

        if (array_key_exists($type, $map)) {
            return $map[$type] ?? [];
        }

        return array_keys(self::editableFields());
    }
}
