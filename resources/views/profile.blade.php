@extends('admin.layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Your Profile</h1>
            <p>Manage your account details and password</p>
        </div>
    </div>

    <div class="container" style="max-width: 720px; padding: 2rem;">
        @if(session('success'))
            <div class="alert alert-success" style="padding: 0.75rem 1rem; border-radius: 6px; background-color: #d1fae5; color: #065f46; margin-bottom: 1.5rem;">
                {{ session('success') }}
            </div>
        @endif

        <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--text-color); margin-bottom: 1.5rem;">Account Details</h2>

                <div style="margin-bottom: 1.25rem;">
                    <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                           style="width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    @error('name')
                        <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                           style="width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    @error('email')
                        <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <p style="font-size: 0.875rem; color: var(--text-color);">
                        Roles:
                        @forelse($user->roles as $role)
                            <span style="display: inline-block; padding: 0.15rem 0.55rem; border-radius: 999px; background-color: var(--border-color); font-size: 0.75rem; margin-left: 0.35rem;">{{ $role->name }}</span>
                        @empty
                            <span style="color: var(--text-color);">None</span>
                        @endforelse
                    </p>
                </div>

                <h2 style="font-size: 1.25rem; font-weight: 600; color: var(--text-color); margin: 2rem 0 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">Change Password</h2>

                <div style="margin-bottom: 1.25rem;">
                    <label for="current_password" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);">Current password</label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                           style="width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    <p style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">Required only when setting a new password.</p>
                    @error('current_password')
                        <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);">New password</label>
                    <input type="password" id="password" name="password" autocomplete="new-password"
                           style="width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px;">
                    @error('password')
                        <p style="color: var(--error-color); font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</p>
                    @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label for="password_confirmation" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--text-color);">Confirm new password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                           style="width: 100%; padding: 0.65rem; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>

                <button type="submit" style="display: inline-block; padding: 0.7rem 1.4rem; border: none; border-radius: 6px; background-color: var(--primary-color); color: #fff; font-weight: 500; cursor: pointer;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
@endsection
