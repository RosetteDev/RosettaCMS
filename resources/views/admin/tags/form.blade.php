@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $action ?? 'Create' }} Tag</h1>
            <p>{{ $action === 'Edit' ? 'Edit your tag' : 'Create a new tag' }}</p>
        </div>
    </div>

    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="{{ $action === 'Edit' ? url('/admin/tags/' . $tag->id) : url('/admin/tags') }}">
                @csrf
                @if($action === 'Edit')
                    @method('PUT')
                @endif
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $tag->name ?? '') }}" required placeholder="e.g., Laravel">
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $tag->slug ?? '') }}" placeholder="auto-generated (e.g., laravel)">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3" placeholder="Tag description...">{{ old('description', $tag->description ?? '') }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="color">Color (Optional)</label>
                    <input type="text" name="color" id="color" value="{{ old('color', $tag->color ?? '') }}" placeholder="#3b82f6" style="font-family: monospace;">
                    <small style="color: #6b7280;">Enter a hex color code (e.g., #3b82f6)</small>
                </div>
                
                <div class="form-group">
                    <label for="order">Order</label>
                    <input type="number" name="order" id="order" value="{{ old('order', $tag->order ?? 0) }}" placeholder="0" style="width: 100px;">
                    <small style="color: #6b7280;">Lower numbers appear first</small>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Save Tag</button>
                    <a href="{{ url('/admin/tags') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
