@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $action ?? 'Create Post' }}</h1>
            <p>{{ $action ?? 'Create a new post' }}</p>
        </div>
    </div>

    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <form method="POST" action="{{ $action === 'Edit' ? url('/admin/posts/' . $post->id) : url('/admin/posts') }}">
                @csrf
                @if($action === 'Edit')
                    @method('PUT')
                @endif
                
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $post->title ?? '') }}" required>
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" name="slug" id="slug" value="{{ old('slug', $post->slug ?? '') }}" placeholder="auto-generated">
                </div>
                
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea name="content" id="content" rows="10">{{ old('content', $post->content ?? '') }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ old('status', $post->status ?? 'draft') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="published" {{ old('status', $post->status ?? 'draft') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="published_at">Published At</label>
                    <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Save Post</button>
                    <a href="{{ url('/admin/posts') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
