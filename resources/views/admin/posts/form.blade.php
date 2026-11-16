@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $action ?? 'Create' }} Post</h1>
            <p>{{ $action === 'Edit' ? 'Edit your existing content' : 'Create a new post' }}</p>
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
                    <div id="quill-editor"></div>
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
                    <label for="categories">Categories</label>
                    <select name="categories[]" id="categories" multiple style="width: 100%; min-height: 80px;">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                @if($post->exists && $post->categories->contains($category->id)) selected @endif
                                {{ old('categories') && in_array($category->id, old('categories')) ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #6b7280;">Hold Ctrl/Cmd to select multiple categories</small>
                </div>
                
                <div class="form-group">
                    <label for="tags">Tags</label>
                    <select name="tags[]" id="tags" multiple style="width: 100%; min-height: 80px;">
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" 
                                @if($post->exists && $post->tags->contains($tag->id)) selected @endif
                                {{ old('tags') && in_array($tag->id, old('tags')) ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #6b7280;">Hold Ctrl/Cmd to select multiple tags</small>
                </div>
                
                <div class="form-group">
                    <label for="published_at">Published At</label>
                    <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\\TH:i') : '') }}">
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Save Post</button>
                    <a href="{{ url('/admin/posts') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
<style>
    #quill-editor {
        min-height: 400px;
        font-family: inherit;
    }
    .ql-toolbar.ql-snow {
        border: 1px solid #d1d5db;
        border-bottom: none;
        border-radius: 0.5rem 0.5rem 0 0;
        background: #f9fafb;
        padding-top: 0.75rem;
        padding-bottom: 0.5rem;
    }
    .ql-toolbar.ql-snow .ql-formats {
        margin-right: 0.5rem;
    }
    .ql-toolbar.ql-snow button {
        padding: 0.25rem 0.5rem;
        margin: 0 0.125rem;
        border-radius: 0.25rem;
    }
    .ql-toolbar.ql-snow button:hover {
        background: #e5e7eb;
    }
    .ql-toolbar.ql-snow button.ql-active {
        background: #eff6ff;
        color: #1e40af;
    }
    .ql-container.ql-snow {
        border: 1px solid #d1d5db;
        border-radius: 0 0 0.5rem 0.5rem;
        background: white;
        font-size: 1rem;
    }
    .ql-editor {
        min-height: 300px;
        padding: 1.5rem;
    }
    .ql-editor.ql-blank::before {
        color: #9ca3af;
        font-style: normal;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['blockquote', 'code-block'],
                [{ align: [] }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        },
        placeholder: 'Compose your content...',
        theme: 'snow'
    });

    // Set initial content
    @if($post->content ?? null)
        quill.root.innerHTML = `<h1><?php echo e($post->title); ?></h1><?php echo $post->content; ?>`;
    @endif

    // Sync on form submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            const textarea = document.getElementById('content');
            if (textarea) {
                textarea.value = quill.root.innerHTML;
            }
        });
    }
});
</script>
@endpush
