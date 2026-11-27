@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $action }} Widget</h1>
            <p>Create or edit a custom widget</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
                    <form method="POST" action="{{ $widget->id ? route('admin.widgets.update', $widget->id) : route('admin.widgets.store') }}">
                        @csrf
                        @if($widget->id)
                            @method('PUT')
                        @endif

                        <div style="margin-bottom: 1.5rem;">
                            <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Widget Name <span style="color: var(--error-color);">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $widget->name) }}" 
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;"
                                   placeholder="Enter widget name" required>
                            @error('name')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="slug" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Widget Slug <span style="color: var(--error-color);">*</span></label>
                            <input type="text" name="slug" id="slug" value="{{ old('slug', $widget->slug) }}" 
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;"
                                   placeholder="enter-widget-slug" required>
                            <small style="color: var(--text-color);">Unique identifier for the widget</small>
                            @error('slug')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="type" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Widget Type <span style="color: var(--error-color);">*</span></label>
                            <select name="type" id="type" 
                                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                                <option value="html" {{ old('type', $widget->type) === 'html' ? 'selected' : '' }}>HTML</option>
                                <option value="rss" {{ old('type', $widget->type) === 'rss' ? 'selected' : '' }}>RSS Feed</option>
                                <option value="custom" {{ old('type', $widget->type) === 'custom' ? 'selected' : '' }}>Custom</option>
                            </select>
                            @error('type')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="location" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Location <span style="color: var(--error-color);">*</span></label>
                            <select name="location" id="location" 
                                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                                <option value="dashboard" {{ old('location', $widget->location) === 'dashboard' ? 'selected' : '' }}>Dashboard (admin)</option>
                                <option value="sidebar" {{ old('location', $widget->location) === 'sidebar' ? 'selected' : '' }}>Sidebar (frontend)</option>
                                <option value="before-footer" {{ old('location', $widget->location) === 'before-footer' ? 'selected' : '' }}>Before footer (frontend)</option>
                                <option value="header" {{ old('location', $widget->location) === 'header' ? 'selected' : '' }}>Header (frontend)</option>
                            </select>
                            @error('location')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="context" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Context <span style="color: var(--error-color);">*</span></label>
                            <select name="context" id="context" 
                                    style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;">
                                <option value="normal" {{ old('context', $widget->context) === 'normal' ? 'selected' : '' }}>Normal</option>
                                <option value="side" {{ old('context', $widget->context) === 'side' ? 'selected' : '' }}>Side</option>
                                <option value="side_full" {{ old('context', $widget->context) === 'side_full' ? 'selected' : '' }}>Side Full</option>
                            </select>
                            @error('context')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="priority" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Priority</label>
                            <input type="number" name="priority" id="priority" value="{{ old('priority', $widget->priority) }}" 
                                   style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem;"
                                   placeholder="10" min="0" max="999">
                            <small style="color: var(--text-color);">Lower numbers appear first (default: 10)</small>
                            @error('priority')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <label for="content" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-color);">Content <span style="color: var(--error-color);">*</span></label>
                            <textarea name="content" id="content" rows="8" 
                                      style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; font-size: 1rem; font-family: monospace;"
                                      placeholder="{{ $widget->type === 'html' ? '<div>Your HTML content here...</div>' : 'Enter widget content...' }}" required>{{ old('content', $widget->content) }}</textarea>
                            <small style="color: var(--text-color);">HTML for HTML widgets, RSS URL for RSS widgets</small>
                            @error('content')
                                <span style="color: var(--error-color); font-size: 0.875rem;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <div style="padding: 0.75rem; background-color: var(--light-color); border: 1px solid var(--border-color); border-radius: 6px;">
                                <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                                    <input type="checkbox" name="enabled" id="enabled" value="1" 
                                           {{ old('enabled', $widget->enabled) ? 'checked' : '' }}
                                           style="width: 20px; height: 20px; margin-right: 0.5rem; cursor: pointer;">
                                    <span style="color: var(--text-color);">Enable this widget</span>
                                </label>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem; font-size: 1rem;">Save Widget</button>
                            <a href="{{ route('admin.widgets.index') }}" class="btn btn-secondary" style="padding: 0.75rem 2rem; font-size: 1rem;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection