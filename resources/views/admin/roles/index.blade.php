@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Roles</h1>
            <p>Define what each role can do — per post type and per field</p>
        </div>
    </div>

    <!-- Actions Bar -->
    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">+ New Role</a>
        <span style="color: var(--text-color); font-size: 0.9rem;">{{ $roles->count() }} role(s)</span>
    </div>

    <!-- Roles Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        <div class="table-responsive"><table style="width: 100%; border-collapse: collapse;">
            <thead style="background-color: var(--dark-color); color: white;">
                <tr>
                    <th style="padding: 1rem; text-align: left;">Name</th>
                    <th style="padding: 1rem; text-align: left;">Slug</th>
                    <th style="padding: 1rem; text-align: left;">Users</th>
                    <th style="padding: 1rem; text-align: left;">Post types</th>
                    <th style="padding: 1rem; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $role)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem;">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                {{ $role->name }}
                            </a>
                            <div style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">
                                {{ Str::limit($role->description ?? '', 80) }}
                            </div>
                        </td>
                        <td style="padding: 1rem;"><code>{{ $role->slug }}</code></td>
                        <td style="padding: 1rem; color: var(--text-color);">{{ $role->users_count }}</td>
                        <td style="padding: 1rem;">
                            @php
                                $pt = $role->post_type_permissions ?? [];
                                $ptCount = count($pt);
                            @endphp
                            @if($ptCount > 0)
                                <span style="display: inline-block; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.8rem; background-color: var(--primary-color); color: white; margin: 0.1rem;">{{ $ptCount }} post type(s)</span>
                            @else
                                <span style="color: var(--text-color); font-size: 0.85rem;">Legacy flat permissions</span>
                            @endif
                        </td>
                        <td style="padding: 1rem; text-align: right;">
                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary" style="padding: 0.4rem 0.9rem;">Edit</a>
                            @if(!in_array($role->slug, ['admin', 'editor', 'author', 'subscriber'], true))
                                <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" style="display: inline;" onsubmit="return confirm('Delete the {{ $role->name }} role?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" style="padding: 0.4rem 0.9rem;" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>
@endsection
