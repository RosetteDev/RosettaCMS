@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>User Profile</h1>
        </div>
    </div>

    <!-- User Profile Card -->
    <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 2rem;">
            <div style="width: 100px; height: 100px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; font-weight: bold;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 style="margin-bottom: 0.5rem;">{{ $user->name }}</h1>
                <p style="color: var(--text-color); font-size: 1.1rem; margin-bottom: 1rem;">{{ $user->email }}</p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <span style="background-color: {{ $user->email_verified_at ? '#10b981' : '#f59e0b' }}; color: white; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 500;">
                        {{ $user->email_verified_at ? 'Active' : 'Pending Verification' }}
                    </span>
                    <span style="background-color: var(--dark-color); color: white; padding: 0.5rem 1rem; border-radius: 4px;">
                        Joined {{ $user->created_at->format('M d, Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- User Information -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        <div class="table-responsive"><table style="width: 100%;">
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1.5rem; width: 200px; font-weight: 500; color: var(--secondary-color);">User ID</td>
                <td style="padding: 1.5rem; font-family: monospace;">{{ $user->id }}</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1.5rem; font-weight: 500; color: var(--secondary-color);">Name</td>
                <td style="padding: 1.5rem;">{{ $user->name }}</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1.5rem; font-weight: 500; color: var(--secondary-color);">Email</td>
                <td style="padding: 1.5rem;">{{ $user->email }}</td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1.5rem; font-weight: 500; color: var(--secondary-color);">Email Verified</td>
                <td style="padding: 1.5rem;">
                    {{ $user->email_verified_at ? $user->email_verified_at->format('M d, Y H:i') : 'Not verified' }}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
                <td style="padding: 1.5rem; font-weight: 500; color: var(--secondary-color);">Last Updated</td>
                <td style="padding: 1.5rem;">{{ $user->updated_at->format('M d, Y H:i') }}</td>
            </tr>
        </table></div>
    </div>

    <!-- Roles Section -->
    <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem; margin-top: 2rem;">
        <h2 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--primary-color);">Roles</h2>
        
        @if($user->roles->count() > 0)
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                @foreach($user->roles as $role)
                    <div style="background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; display: flex; align-items: center; gap: 0.5rem;">
                        <span>{{ $role->name }}</span>
                        @if($role->description)
                            <span style="font-size: 0.85rem; opacity: 0.8;">- {{ $role->description }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 3rem; color: var(--secondary-color);">
                <p>This user has no roles assigned.</p>
            </div>
        @endif
    </div>

    <!-- User Actions -->
    <div style="margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="{{ url('/admin/users/' . $user->id . '/edit') }}" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-size: 1.1rem;">Edit User</a>
        <a href="{{ url('/admin/users') }}" class="btn btn-outline" style="padding: 0.75rem 1.5rem;">Back to Users</a>
    </div>
@endsection
