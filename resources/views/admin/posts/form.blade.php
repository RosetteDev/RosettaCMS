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
                    <div id="editor-container"></div>
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
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/@tiptap/pm@2.4.0/dist/style.css" rel="stylesheet">
<style>
    .ProseMirror {
        min-height: 400px;
        padding: 1rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
    }
    .ProseMirror p {
        margin-bottom: 1rem;
    }
    .ProseMirror h1, .ProseMirror h2, .ProseMirror h3 {
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
    }
    .ProseMirror ul, .ProseMirror ol {
        margin-left: 1.5rem;
        margin-bottom: 1rem;
    }
    .ProseMirror blockquote {
        border-left: 4px solid #6b7280;
        padding-left: 1rem;
        margin-left: 0;
        color: #6b7280;
    }
    .ProseMirror img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
    }
    .tiptap-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0.5rem 0.5rem 0 0;
    }
    .tiptap-toolbar button {
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        background: white;
        border-radius: 0.375rem;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .tiptap-toolbar button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }
    .tiptap-toolbar button.is-active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@tiptap/core@2.4.0/dist/tiptap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/starter-kit@2.4.0/dist/tiptap.starter-kit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@tiptap/extension-image@2.4.0/dist/tiptap.extension-image.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editor = new Tiptap.Editor({
        element: document.querySelector('#editor-container'),
        extensions: [
            Tiptap.StarterKit.configure({
                bulletList: {
                    HTMLAttributes: {
                        class: 'list-disc list-outside ml-6',
                    },
                },
                orderedList: {
                    HTMLAttributes: {
                        class: 'list-decimal list-outside ml-6',
                    },
                },
                blockquote: {
                    HTMLAttributes: {
                        class: 'border-l-4 border-gray-500 pl-4 italic text-gray-600',
                    },
                },
                heading: {
                    levels: [1, 2, 3],
                },
            }),
            Tiptap.Image.configure({
                HTMLAttributes: {
                    class: 'rounded-lg',
                },
            }),
        ],
        content: @if($post->content ?? null) '{!! $post->content !!}' @else '' @endif,
        onUpdate: ({ editor }) => {
            document.getElementById('content').value = editor.getHTML();
        },
    });

    // Toolbar buttons
    const toolbar = document.createElement('div');
    toolbar.className = 'tiptap-toolbar';
    
    const buttons = [
        { label: 'H1', command: () => editor.chain().focus().toggleHeading({ level: 1 }).run(), active: () => editor.isActive('heading', { level: 1 }) },
        { label: 'H2', command: () => editor.chain().focus().toggleHeading({ level: 2 }).run(), active: () => editor.isActive('heading', { level: 2 }) },
        { label: 'H3', command: () => editor.chain().focus().toggleHeading({ level: 3 }).run(), active: () => editor.isActive('heading', { level: 3 }) },
        { label: 'B', command: () => editor.chain().focus().toggleBold().run(), active: () => editor.isActive('bold') },
        { label: 'I', command: () => editor.chain().focus().toggleItalic().run(), active: () => editor.isActive('italic') },
        { label: 'UL', command: () => editor.chain().focus().toggleBulletList().run(), active: () => editor.isActive('bulletList') },
        { label: 'OL', command: () => editor.chain().focus().toggleOrderedList().run(), active: () => editor.isActive('orderedList') },
        { label: 'Quote', command: () => editor.chain().focus().toggleBlockquote().run(), active: () => editor.isActive('blockquote') },
        { 
            label: 'Img', 
            command: () => {
                const url = prompt('Enter image URL:');
                if (url) editor.chain().focus().setImage({ src: url }).run();
            }
        },
    ];

    buttons.forEach(btn => {
        const button = document.createElement('button');
        button.textContent = btn.label;
        button.onclick = btn.command;
        button.className = 'is-active';
        button.style.display = 'none';
        
        // Update active state
        editor.on('selectionUpdate', () => {
            if (btn.active()) {
                button.classList.add('is-active');
                button.style.display = 'inline-flex';
            } else {
                button.classList.remove('is-active');
                button.style.display = 'inline-flex';
            }
        });
        
        toolbar.appendChild(button);
    });

    editor.on('focus', () => {
        if (!editor.getHTML().trim()) {
            editor.chain().focus().setParagraph().run();
        }
    });

    // Insert toolbar before editor
    editor.element.parentNode.insertBefore(toolbar, editor.element);

    // Sync on form submit
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('content').value = editor.getHTML();
    });

    window.tiptapEditor = editor;
});
</script>
@endpush
