@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $action }} Role</h1>
            <p>{{ $role->id ? 'Update role details and permissions' : 'Create a new role' }}</p>
        </div>
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
        <form action="{{ $role->id ? url('/admin/roles/' . $role->id) : url('/admin/roles') }}" method="POST">
            @csrf
            @if($role->id)
                @method('PUT')
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Basic details -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required maxlength="100" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 6px; background-color: white;">
                </div>
                <div>
                    <label for="slug" class="form-label">Slug <span style="font-weight: normal; color: var(--text-muted);">(optional)</span></label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $role->slug) }}" placeholder="auto-generated-from-name" maxlength="100" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 6px; background-color: white;">
                </div>
            </div>
            <div style="margin-bottom: 2rem;">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="2" maxlength="500" style="width: 100%; padding: 0.6rem; border: 1px solid var(--border-color); border-radius: 6px; background-color: white;">{{ old('description', $role->description) }}</textarea>
            </div>

            <!-- Flat permissions -->
            <h2 style="font-size: 1.1rem; margin: 0 0 1rem; color: var(--text-color);">Flat permissions</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 2rem;">
                @foreach(['dashboard' => 'Dashboard', 'posts' => 'Posts', 'pages' => 'Pages', 'media' => 'Media', 'users' => 'Users', 'settings' => 'Settings', 'plugins' => 'Plugins', 'themes' => 'Themes', 'seo' => 'SEO', 'multi_site' => 'Multi-site', 'comments' => 'Comments'] as $key => $label)
                    <label style="display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.8rem; border: 1px solid var(--border-color); border-radius: 6px; background-color: white; cursor: pointer; font-size: 0.9rem;">
                        <input type="checkbox" name="permissions[{{ $key }}]" value="1" {{ old("permissions.$key", $role->permissions[$key] ?? false) ? 'checked' : '' }} style="margin: 0;">
                        {{ $label }}
                    </label>
                @endforeach
            </div>

            <!-- Per-post-type permissions -->
            <h2 style="font-size: 1.1rem; margin: 0 0 1rem; color: var(--text-color);">Per-post-type permissions</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 1rem;">Leave a post type unchecked to fall back to the legacy flat permission (posts/pages).</p>
            <div style="overflow-x: auto; margin-bottom: 2rem;">
                <table style="width: 100%; border-collapse: collapse; background-color: white; border: 1px solid var(--border-color); border-radius: 8px;">
                    <thead>
                        <tr style="background-color: var(--dark-color); color: white;">
                            <th style="padding: 0.75rem; text-align: left;">Post type</th>
                            <th style="padding: 0.75rem; text-align: center;">View</th>
                            <th style="padding: 0.75rem; text-align: center;">Create</th>
                            <th style="padding: 0.75rem; text-align: center;">Edit</th>
                            <th style="padding: 0.75rem; text-align: center;">Delete</th>
                            <th style="padding: 0.75rem; text-align: center;">Publish</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($postTypes as $type)
                            @php
                                $typeDef = $type === 'page' ? null : \App\Support\PostTypes::get($type);
                                $label = $typeDef['label'] ?? ucfirst($type);
                                $perms = ($role->post_type_permissions ?? [])[$type] ?? [];
                            @endphp
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem; font-weight: 500;">{{ $label }}</td>
                                @foreach(['view', 'create', 'edit', 'delete', 'publish'] as $action)
                                    <td style="padding: 0.75rem; text-align: center;">
                                        <input type="checkbox" name="post_type_permissions[{{ $type }}][{{ $action }}]" value="1" {{ old("post_type_permissions.$type.$action", $perms[$action] ?? false) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Per-field permissions -->
            <h2 style="font-size: 1.1rem; margin: 0 0 1rem; color: var(--text-color);">Per-field permissions</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0 0 1rem;">Which fields can this role edit on each post type? Leave empty to allow all editable fields.</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                @foreach($postTypes as $type)
                    @php
                        $typeDef = $type === 'page' ? null : \App\Support\PostTypes::get($type);
                        $label = $typeDef['label'] ?? ucfirst($type);
                        $fields = ($role->field_permissions ?? [])[$type] ?? [];
                    @endphp
                    <div style="background-color: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
                        <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ $label }}</div>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                            @foreach($editableFields as $field => $fieldLabel)
                                <label style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.2rem 0.5rem; border-radius: 6px; font-size: 0.8rem; cursor: pointer;
                                    {{ in_array($field, old("field_permissions.$type", $fields), true) ? 'background-color: var(--primary-color); color: white;' : 'background-color: var(--light-color);' }}">
                                    <input type="checkbox" name="field_permissions[{{ $type }}][]" value="{{ $field }}" {{ in_array($field, old("field_permissions.$type", $fields), true) ? 'checked' : '' }} style="margin: 0;">
                                    {{ $fieldLabel }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 1rem; justify-content: flex-end; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $action }} Role</button>
            </div>
        </form>
    </div>
@endsection
