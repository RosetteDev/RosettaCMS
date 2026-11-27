<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the default roles (admin/editor/author) with per-post-type and
 * per-field permissions on existing installs. New installs get the same
 * defaults from the seeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        $allTypes = ['post', 'portfolio', 'testimonial', 'event', 'page'];

        $admin = ['view' => true, 'create' => true, 'edit' => true, 'delete' => true, 'publish' => true];
        $editor = ['view' => true, 'create' => true, 'edit' => true, 'delete' => false, 'publish' => true];
        $authorTypes = ['post', 'portfolio', 'testimonial', 'event'];

        $adminPerms = [];
        foreach ($allTypes as $type) {
            $adminPerms[$type] = $admin;
        }

        $editorPerms = [];
        foreach ($allTypes as $type) {
            $editorPerms[$type] = $editor;
        }

        $authorPerms = [];
        foreach ($authorTypes as $type) {
            $authorPerms[$type] = ['view' => true, 'create' => true, 'edit' => true, 'delete' => false, 'publish' => false];
        }

        $authorFields = [];
        foreach ($authorTypes as $type) {
            $authorFields[$type] = $type === 'testimonial'
                ? ['title', 'slug', 'content', 'excerpt']
                : ['title', 'slug', 'content', 'excerpt', 'featured_image'];
        }

        $this->updateRole('admin', $adminPerms, null);
        $this->updateRole('editor', $editorPerms, null);
        $this->updateRole('author', $authorPerms, $authorFields);
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('slug', ['admin', 'editor', 'author'])->update([
            'post_type_permissions' => null,
            'field_permissions' => null,
        ]);
    }

    private function updateRole(string $slug, array $postTypePermissions, ?array $fieldPermissions): void
    {
        $role = DB::table('roles')->where('slug', $slug)->first();

        if (! $role) {
            return;
        }

        // Only fill the per-post-type permissions when the role has none yet,
        // so a role the user already customized is never overwritten.
        $existing = $role->post_type_permissions ?? null;

        if ($existing === null || $existing === '' || json_decode($existing, true) === []) {
            DB::table('roles')->where('slug', $slug)->update([
                'post_type_permissions' => json_encode($postTypePermissions),
                'field_permissions' => $fieldPermissions ? json_encode($fieldPermissions) : null,
            ]);
        }
    }
};
