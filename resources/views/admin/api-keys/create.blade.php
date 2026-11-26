@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>New API Key</h1>
            <p>Create a key for authenticating against the public API.</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
                    <form method="POST" action="{{ route('admin.api-keys.store') }}">
                        @csrf

                        <div style="margin-bottom: 1.5rem;">
                            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Key name <span style="color: var(--error-color);">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px;" placeholder="e.g. My mobile app" required>
                            @error('name')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Scopes</label>
                            <p class="text-muted small mb-2">Choose what this key may do. A read-only key is a good default.</p>
                            <div class="row g-2">
                                @foreach($availableScopes as $scope)
                                    <div class="col-md-6">
                                        <label class="form-check d-flex align-items-center" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: 6px;">
                                            <input type="checkbox" class="form-check-input me-2" name="scopes[]" value="{{ $scope }}" {{ in_array($scope, old('scopes', [])) ? 'checked' : '' }}>
                                            <code>{{ $scope }}</code>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('scopes')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="expires_at" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Expires at <span class="text-muted small">(optional)</span></label>
                            <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at') }}" min="{{ now()->addDay()->toDateString() }}" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px;">
                            @error('expires_at')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create API Key</button>
                            <a href="{{ route('admin.api-keys.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
