@php
    $statusLabels = [
        'active' => ['label' => 'Active', 'color' => '#10b981'],
        'pending' => ['label' => 'Pending', 'color' => '#f59e0b'],
    ];
    
    $status = $user->email_verified_at ? $statusLabels['active'] : $statusLabels['pending'];
@endphp

@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $action }} User</h1>
            <p>{{ $user->id ? 'Manage user details and permissions' : 'Create a new user account' }}</p>
        </div>
    </div>

    <!-- User Details Card -->
    <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem; margin-bottom: 2rem;">
        @if($user->id)
            <div style="display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border-color);">
                <div style="width: 80px; height: 80px; background-color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: bold;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 style="margin-bottom: 0.5rem;">{{ $user->name }}</h2>
                    <p style="color: var(--text-color); margin-bottom: 0.5rem;">{{ $user->email }}</p>
                    <span style="display: inline-block; background-color: {{ $status['color'] }}20; color: {{ $status['color'] }}; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem; border: 1px solid {{ $status['color'] }}40;">
                        {{ $status['label'] }}
                    </span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div style="background-color: white; padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="color: var(--secondary-color); font-size: 0.85rem; margin-bottom: 0.25rem;">User ID</div>
                    <div style="font-family: monospace; font-size: 1.1rem;">{{ $user->id }}</div>
                </div>
                <div style="background-color: white; padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="color: var(--secondary-color); font-size: 0.85rem; margin-bottom: 0.25rem;">Joined</div>
                    <div>{{ $user->created_at->format('M d, Y') }}</div>
                </div>
                <div style="background-color: white; padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="color: var(--secondary-color); font-size: 0.85rem; margin-bottom: 0.25rem;">Last Login</div>
                    <div>{{ $user->updated_at->format('M d, Y') }}</div>
                </div>
            </div>
        @endif

        <!-- User Form -->
        <form action="{{ $user->id ? url('/admin/users/' . $user->id) : url('/admin/users') }}" method="POST">
            @csrf
            @if($user->id)
                @method('PUT')
            @endif

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <!-- Basic Information -->
                <div style="background-color: white; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
                    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--primary-color);">Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="name">Name <span style="color: var(--danger-color);">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span style="color: var(--danger-color);">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255">
                        <div style="color: #9ca3af; font-size: 0.85rem; margin-top: 0.25rem;">
                            @if($user->email_verified_at)
                                ✅ Email verified
                            @else
                                ⏳ Email not verified
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password @if($user->id)<span style="color: #9ca3af; font-size: 0.85rem;">(leave blank to keep current)</span>@endif</label>
                        <input type="password" id="password" name="password" {{ $user->id ? '' : 'required' }} maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" {{ $user->id ? '' : 'required' }} maxlength="255">
                    </div>
                </div>

                <!-- Roles -->
                <div style="background-color: white; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color);">
                    <h3 style="margin-bottom: 1.5rem; padding-bottom: 0.75rem; border-bottom: 2px solid var(--primary-color);">Roles & Permissions</h3>
                    
                    <div class="form-group">
                        <label>Assign Roles</label>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            @foreach($roles as $role)
                                <label style="display: flex; align-items: center; padding: 0.75rem; background-color: var(--light-color); border-radius: 6px; cursor: pointer; transition: background-color 0.2s;">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" 
                                           {{ $user->roles->contains($role->id) ? 'checked' : '' }}
                                           style="width: 18px; height: 18px; margin-right: 0.75rem; accent-color: var(--primary-color);">
                                    <div>
                                        <strong>{{ $role->name }}</strong>
                                        @if($role->description)
                                            <div style="font-size: 0.85rem; color: var(--secondary-color); margin-top: 0.25rem;">{{ $role->description }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div style="background-color: var(--info-color)20; border-left: 4px solid var(--info-color); padding: 1rem; border-radius: 4px; margin-top: 1.5rem;">
                        <h4 style="color: var(--info-color); margin-bottom: 0.5rem;">ℹ️ About Roles</h4>
                        <p style="color: var(--text-color); font-size: 0.9rem; margin: 0;">
                            Users can have multiple roles. Roles determine what actions a user can perform on the site.
                            <br>The <strong>Administrator</strong> role has full access to all features.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end; flex-wrap: wrap;">
                @if($user->id)
                    <a href="{{ url('/admin/users') }}" class="btn btn-outline" style="padding: 0.75rem 1.5rem;">Cancel</a>
                    
                    <form action="{{ url('/admin/users/' . $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="padding: 0.75rem 1.5rem;">Delete User</button>
                    </form>
                @endif
                
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-size: 1.1rem;">
                    {{ $user->id ? 'Update User' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
@endsection
