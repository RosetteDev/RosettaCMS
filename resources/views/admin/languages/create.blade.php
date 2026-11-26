@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Add Language</h1>
            <p>Create a new language for your site.</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
                    <form method="POST" action="{{ route('admin.languages.store') }}">
                        @csrf

                        <div style="margin-bottom: 1rem;">
                            <label for="code" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Code <span style="color: var(--error-color);">*</span></label>
                            <input type="text" name="code" id="code" value="{{ old('code') }}" maxlength="2" placeholder="fr" required
                                   style="width: 8rem; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; text-transform: lowercase;">
                            <div class="form-text">ISO 639-1 code, e.g. <code>en</code>, <code>fr</code>, <code>ar</code>.</div>
                            @error('code')<span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>@enderror
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Name <span style="color: var(--error-color);">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="French" required
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px;">
                            @error('name')<span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>@enderror
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label for="native_name" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Native name</label>
                            <input type="text" name="native_name" id="native_name" value="{{ old('native_name') }}" placeholder="Français"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px;">
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label for="flag" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Flag / emoji</label>
                            <input type="text" name="flag" id="flag" value="{{ old('flag') }}" placeholder="🇫🇷"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px;">
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label for="sort_order" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Sort order</label>
                            <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                                   style="width: 8rem; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px;">
                        </div>

                        <div class="form-check mb-2">
                            <input type="checkbox" name="is_default" id="is_default" value="1" class="form-check-input" {{ old('is_default') ? 'checked' : '' }}>
                            <label for="is_default" class="form-check-label">Default language</label>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_active" id="is_active" value="1" class="form-check-input" checked>
                            <label for="is_active" class="form-check-label">Active (enabled on the frontend)</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Language</button>
                            <a href="{{ route('admin.languages.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
