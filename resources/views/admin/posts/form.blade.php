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
                    <div id="editor-container" style="height: 300px;"></div>
                    <textarea name="content" id="content" style="display: none;"></textarea>
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

    @push('styles')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <style>
        .ql-container {
            font-size: 16px;
            min-height: 300px;
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.quilljs.com/1.3.7/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline'],
                    [{ list: 'ordered'}, { list: 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        // Sync Quill content to textarea on form submit
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById('content').value = quill.root.innerHTML;
        });

        // Initialize with existing content if editing
        @if($post->content ?? null)
        quill.root.innerHTML = '{!! $post->content !!}';
        @endif
    </script>
    @endpush
@endsection
