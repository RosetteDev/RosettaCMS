@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Pending Users</h1>
            <p>Users awaiting email verification</p>
        </div>
    </div>

    <!-- Filters -->
    <div style="background-color: var(--light-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <form method="GET" action="{{ url('/admin/users/pending') }}" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="search" style="margin-bottom: 0.25rem; font-size: 0.85rem;">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name or email" style="padding: 0.5rem; border-radius: 6px; border: 1px solid var(--border-color);">
            </div>
            
            <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Filter</button>
            @if(request()->has('search'))
                <a href="{{ url('/admin/users/pending') }}" class="btn btn-outline" style="padding: 0.5rem 1rem;">Clear</a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($users) > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-color); color: white;">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">User</th>
                        <th style="padding: 1rem; text-align: left;">Email</th>
                        <th style="padding: 1rem; text-align: left;">Roles</th>
                        <th style="padding: 1rem; text-align: left;">Joined</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ $user->name }}
                                </a>
                                <div style="font-size: 0.85rem; color: #9ca3af;">ID: {{ $user->id }}</div>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $user->email }}
                            </td>
                            <td style="padding: 1rem;">
                                @if($user->roles->count() > 0)
                                    @foreach($user->roles->take(3) as $role)
                                        <span style="display: inline-block; background-color: var(--primary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; margin-right: 0.25rem; margin-bottom: 0.25rem;">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                    @if($user->roles->count() > 3)
                                        <span style="display: inline-block; background-color: var(--secondary-color); color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                            +{{ $user->roles->count() - 3 }}
                                        </span>
                                    @endif
                                @else
                                    <span style="color: #9ca3af;">No roles</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; color: var(--text-color); font-size: 0.85rem;">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Edit</a>
                                    <a href="{{ url('/admin/users/' . $user->id . '/verify') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.85rem; background-color: #10b981; border-color: #10b981;" onclick="return confirm('Verify this user and activate their account?')">
                                        Verify
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ url('/admin/users/' . $user->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to delete this user?')">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>All users verified! <a href="{{ url('/admin/users') }}" style="color: var(--primary-color);">View all users</a>.</p>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    <div style="padding: 1.5rem 0; display: flex; justify-content: center;">
        {{ $users->links() }}
    </div>
@endsection
