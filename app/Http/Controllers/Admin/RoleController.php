<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Admin management of roles. Supports flat permissions plus per-post-type
 * and per-field granularity (e.g. an editor who can view and edit posts but
 * never delete them, or edit only the title/content/excerpt fields).
 */
class RoleController extends Controller
{
    /**
     * Roles that cannot be deleted (they are part of the default seed).
     */
    protected array $protectedSlugs = ['admin', 'editor', 'author', 'subscriber'];

    public function index()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('admin.roles.index', [
            'roles' => $roles,
            'postTypes' => Role::postTypes(),
            'editableFields' => Role::editableFields(),
        ]);
    }

    public function create()
    {
        return view('admin.roles.form', [
            'action' => 'Create',
            'role' => new Role(),
            'postTypes' => Role::postTypes(),
            'editableFields' => Role::editableFields(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        $role = Role::create($validated);

        return redirect()->route('admin.roles.edit', $role->id)
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.form', [
            'action' => 'Edit',
            'role' => $role,
            'postTypes' => Role::postTypes(),
            'editableFields' => Role::editableFields(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $this->validateRole($request, $role);

        $role->update($validated);

        return redirect()->route('admin.roles.edit', $role->id)
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->slug, $this->protectedSlugs, true)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'The default role cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Reassign or remove the users assigned to this role before deleting it.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Validate and normalize the submitted role payload.
     */
    protected function validateRole(Request $request, ?Role $role = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => array_filter(['nullable', 'string', 'max:100', 'alpha_dash', $role ? 'unique:roles,slug,' . $role->id : 'unique:roles,slug']),
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'permissions.*' => 'boolean',
            'post_type_permissions' => 'nullable|array',
            'post_type_permissions.*' => 'array',
            'post_type_permissions.*.view' => 'nullable|boolean',
            'post_type_permissions.*.create' => 'nullable|boolean',
            'post_type_permissions.*.edit' => 'nullable|boolean',
            'post_type_permissions.*.delete' => 'nullable|boolean',
            'post_type_permissions.*.publish' => 'nullable|boolean',
            'field_permissions' => 'nullable|array',
            'field_permissions.*' => 'array',
            'field_permissions.*.*' => 'string|max:100',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        // Normalize flat permissions to booleans.
        $validated['permissions'] = array_map(fn ($v) => (bool) $v, $validated['permissions'] ?? []);

        // Normalize per-post-type permissions: drop empty/unset entries.
        $postTypePerms = [];
        foreach ($validated['post_type_permissions'] ?? [] as $type => $actions) {
            if (!array_key_exists($type, \App\Support\PostTypes::all()) && $type !== 'page') {
                continue;
            }
            $actions = is_array($actions) ? $actions : [];
            $normalized = [
                'view' => (bool) ($actions['view'] ?? false),
                'create' => (bool) ($actions['create'] ?? false),
                'edit' => (bool) ($actions['edit'] ?? false),
                'delete' => (bool) ($actions['delete'] ?? false),
                'publish' => (bool) ($actions['publish'] ?? false),
            ];
            // Only keep the type if at least one action is allowed.
            if (array_filter($normalized)) {
                $postTypePerms[$type] = $normalized;
            }
        }
        $validated['post_type_permissions'] = $postTypePerms;

        // Normalize per-field permissions: keep only known fields per type.
        $knownFields = array_keys(Role::editableFields());
        $fieldPerms = [];
        foreach ($validated['field_permissions'] ?? [] as $type => $fields) {
            if (!array_key_exists($type, \App\Support\PostTypes::all()) && $type !== 'page') {
                continue;
            }
            $fields = is_array($fields) ? array_unique($fields) : [];
            $fields = array_values(array_filter($fields, fn ($f) => in_array($f, $knownFields, true)));
            if ($fields) {
                $fieldPerms[$type] = $fields;
            }
        }
        $validated['field_permissions'] = $fieldPerms;

        return $validated;
    }
}
