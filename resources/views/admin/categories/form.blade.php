@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $action ?? 'Create' }} Category</h1>
            <p>{{ $action === 'Edit' ? 'Edit your category' : 'Create a new category' }}</p>
        </div>
    </div>

    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="{{ $action === 'Edit' ? url('/admin/categories/' . $category->slug) : url('/admin/categories') }}">
                @csrf
                @if($action === 'Edit')
                    @method('PUT')
                @endif
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name ?? '') }}" required placeholder="e.g., Technology">
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $category->slug ?? '') }}" placeholder="auto-generated (e.g., technology)">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="3" placeholder="Category description...">{{ old('description', $category->description ?? '') }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="color">Color (Optional)</label>
                    <input type="text" name="color" id="color" value="{{ old('color', $category->color ?? '') }}" placeholder="#3b82f6" style="font-family: monospace;">
                    <small style="color: #6b7280;">Enter a hex color code (e.g., #3b82f6)</small>
                </div>
                
                <div class="form-group">
                    <label for="order">Order</label>
                    <input type="number" name="order" id="order" value="{{ old('order', $category->order ?? 0) }}" placeholder="0" style="width: 100px;">
                    <small style="color: #6b7280;">Lower numbers appear first</small>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Save Category</button>
                    <a href="{{ url('/admin/categories') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
