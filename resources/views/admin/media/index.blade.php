@extends('admin.layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@6.0.0-beta.2/dist/basic.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dropzone@6.0.0-beta.2/dist/dropzone.css">
<style>
    /* Dropzone Styles */
    .dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        background: #fafafa;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        margin-bottom: 1.5rem;
        position: relative !important;
        z-index: 1 !important;
        overflow: visible !important;
    }

    .dropzone .dz-preview {
        position: relative !important;
        z-index: 1 !important;
    }

    .dropzone .dz-preview .dz-progress {
        position: absolute !important;
        bottom: 0;
        left: 0;
    }

    /* Ensure Dropzone container doesn't get fixed positioning */
    .dropzone-form-container {
        position: relative !important;
        z-index: 1 !important;
        overflow: visible !important;
    }

    /* Dropzone wrapper element */
    .dropzone .dz-button {
        position: relative !important;
    }

    /* Dropzone preview wrapper */
    .dropzone .dz-preview {
        position: relative !important;
        z-index: 1 !important;
        background: transparent !important;
    }

    /* Dropzone's file preview container */
    .dropzone .dz-file-preview {
        position: relative !important;
        z-index: 1 !important;
    }

    /* Dropzone's main wrapper - prevent fixed positioning */
    .dropzone.dz-clickable {
        position: relative !important;
        z-index: 1 !important;
        overflow: visible !important;
    }

    /* Dropzone's hidden input wrapper */
    .dropzone .dz-hidden-input {
        position: absolute !important;
        z-index: -1 !important;
        opacity: 0;
        top: auto;
        left: auto;
    }

    .dropzone:hover, .dropzone.dragover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .dropzone i {
        font-size: 3rem;
        color: #94a3b8;
        margin-bottom: 1rem;
    }

    .dropzone p {
        color: #64748b;
        margin: 0;
    }

    /* Media Grid */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1.5rem;
    }

    .media-card {
        position: relative;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
    }

    .media-card:hover {
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .media-card.selected {
        border-color: #3b82f6;
    }

    .media-card input.select-checkbox {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 10;
        opacity: 0;
        pointer-events: none;
    }

    .media-card.selected input.select-checkbox + label {
        opacity: 1;
    }

    .media-card .select-checkbox-label {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 10;
        width: 24px;
        height: 24px;
        background: white;
        border: 2px solid #cbd5e1;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .media-card.selected .select-checkbox-label {
        background: #3b82f6;
        border-color: #3b82f6;
    }

    .media-card .select-checkbox-label::after {
        content: '✓';
        color: white;
        font-size: 16px;
        font-weight: bold;
        display: none;
    }

    .media-card.selected .select-checkbox-label::after {
        display: block;
    }

    .media-preview {
        position: relative;
        height: 160px;
        overflow: hidden;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .media-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .media-preview .file-icon {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
    }

    .media-preview .file-icon svg {
        width: 64px;
        height: 64px;
        color: #94a3b8;
    }

    .media-content {
        padding: 1rem;
    }

    .media-name {
        font-weight: 600;
        font-size: 0.875rem;
        color: #1e293b;
        margin-bottom: 0.25rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .media-name a {
        color: #2563eb;
        text-decoration: none;
    }

    .media-name a:hover {
        text-decoration: underline;
    }

    .media-info {
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .folder-tag {
        display: inline-block;
        background: #e0f2fe;
        color: #0369a1;
        padding: 0.125rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
    }

    .folder-tag a {
        color: #0369a1;
        text-decoration: none;
    }

    .folder-tag a:hover {
        text-decoration: underline;
    }

    .bulk-actions {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        margin-bottom: 1.5rem;
        display: none;
        align-items: center;
        gap: 1rem;
    }

    .bulk-actions.show {
        display: flex;
        justify-content: space-between;
    }

    .bulk-actions .selected-count {
        font-weight: 600;
        color: #1e293b;
        background: #e0f2fe;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
    }

    .bulk-actions .actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .bulk-actions button {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 6px;
    }

    .bulk-actions select {
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: white url('data:image/svg+xml;utf8,<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>') no-repeat right 0.75rem center;
        background-size: 1rem;
        cursor: pointer;
    }

    .filters {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }

    .filter-group {
        flex: 1;
        min-width: 150px;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .filter-group label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .filter-group input, .filter-group select {
        padding: 0.5rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.875rem;
        width: 100%;
    }

    .filter-group input:focus, .filter-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 0.5rem;
    }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #64748b;
        grid-column: 1 / -1;
    }

    .empty-state svg {
        width: 80px;
        height: 80px;
        margin-bottom: 1rem;
        color: #cbd5e1;
    }

    .upload-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: #3b82f6;
        width: 0;
        transition: width 0.3s ease;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 100;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        max-width: 400px;
        width: 90%;
        margin: 1rem;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.25rem;
        color: #1e293b;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #64748b;
        line-height: 1;
    }

    .modal-body {
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
    }

    .form-group input, .form-group select {
        width: 100%;
        padding: 0.625rem;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 0.875rem;
    }

    .form-group input:focus, .form-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }

    .btn-secondary {
        background: white;
        border: 1px solid #cbd5e1;
        color: #1e293b;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
    }

    .btn-secondary:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .btn-primary {
        background: #2563eb;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        border: none;
    }

    .btn-primary:hover {
        background: #1d4ed8;
    }

    .btn-danger {
        background: #dc2626;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        border: none;
    }

    .btn-danger:hover {
        background: #b91c1c;
    }

    .btn-success {
        background: #059669;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        border: none;
    }

    .btn-success:hover {
        background: #047857;
    }

    .card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        overflow: visible !important;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .card-header h2 {
        margin: 0;
        font-size: 1.25rem;
        color: #1e293b;
    }

    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 3rem 2rem;
        text-align: center;
        background: #fafafa;
        transition: all 0.3s ease;
    }

    .upload-zone:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .upload-zone p {
        color: #64748b;
        margin: 0.5rem 0;
    }

    .upload-zone .upload-icon {
        font-size: 3rem;
        color: #94a3b8;
        margin-bottom: 1rem;
    }

    .actions-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .actions-left, .actions-right {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .btn-icon {
        background: white;
        border: 1px solid #cbd5e1;
        width: 36px;
        height: 36px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.2s;
    }

    .btn-icon:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .audio-preview {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 1rem;
        width: 100%;
        height: 100%;
        background: #f8fafc;
    }

    .audio-preview audio {
        width: 100%;
        max-width: 220px;
        height: 36px;
    }

    .btn-icon-danger {
        color: #dc2626;
        border-color: #dc2626;
    }

    .btn-icon-danger:hover {
        background: #fee2e2;
    }

    .btn-icon-secondary {
        color: #64748b;
    }

    .btn-icon-secondary:hover {
        color: #475569;
    }
</style>
@endpush

@section('title', 'Media Library')

@section('content')
    <div class="actions-bar">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Media Library</h1>
            <p class="text-gray-600 text-sm mt-1">Manage all your uploaded files</p>
        </div>
        <button onclick="document.getElementById('file-input').click()" class="btn btn-primary">
            Add New
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6 flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Bulk Actions Toolbar -->
    <div id="bulk-actions" class="bulk-actions">
        <div class="flex items-center gap-3">
            <span id="selected-count" class="font-medium text-gray-800 bg-blue-50 px-3 py-1 rounded-full text-sm">
                0 items selected
            </span>
        </div>
        <div class="actions">
            <select id="bulk-folder-select">
                <option value="">Move to folder...</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder }}">{{ $folder }}</option>
                @endforeach
                <option value="__new__">Create new folder...</option>
            </select>
            <button onclick="showBulkRenameModal()" class="btn btn-secondary">Rename</button>
            <button onclick="showBulkAltModal()" class="btn btn-secondary">Set alt text</button>
            <button onclick="bulkDelete()" class="btn btn-danger">Delete</button>
            <button onclick="bulkMoveToFolder()" class="btn btn-primary">Move</button>
            <button onclick="deselectAll()" class="btn btn-secondary">Cancel</button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" id="search-input" placeholder="Search files..." value="{{ request('search', '') }}" onkeypress="handleEnter(event)">
        </div>
        <div class="filter-group">
            <label>Folder</label>
            <select id="folder-filter" onchange="applyFilters()">
                <option value="">All Folders</option>
                @foreach($folders as $folder)
                    <option value="{{ $folder }}" {{ request('folder') == $folder ? 'selected' : '' }}>{{ $folder }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label>Type</label>
            <select id="type-filter" onchange="applyFilters()">
                <option value="">All Types</option>
                <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images</option>
                <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Videos</option>
                <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Documents</option>
            </select>
        </div>
        <div class="filter-actions">
            <button onclick="resetFilters()" class="btn btn-secondary">Reset</button>
        </div>
    </div>

    <!-- Upload Zone -->
    <div class="card dropzone-form-container">
        <div class="card-header">
            <h2>Upload Media</h2>
        </div>
        <form action="{{ route('admin.media.store') }}" class="dropzone" id="dropzone-form">
            <input type="file" name="file[]" id="file-input" multiple style="display: none;" accept="image/*,video/*,application/pdf">
            <div class="upload-zone">
                <div class="upload-icon"> cloud_upload </div>
                <p><strong>Drag & drop files here</strong> to upload</p>
                <p class="text-sm">or <strong>click to browse</strong> from your computer</p>
                <p class="text-xs text-gray-500 mt-2">Supported formats: JPG, PNG, GIF, MP4, PDF (Max 2MB)</p>
            </div>
            <div class="form-group mt-3">
                <label for="alt-text-batch" class="form-label">Alt text <span class="text-xs text-gray-500">(required for images — applied to the whole batch)</span></label>
                <input type="text" name="alt_text" id="alt-text-batch" class="form-input" maxlength="255" placeholder="Describe the image…">
                <p class="text-xs text-gray-500 mt-1">Alt text is required for image uploads for accessibility and SEO.</p>
            </div>
        </form>
    </div>

    <!-- Media Grid -->
    <div id="media-grid" class="media-grid">
        @forelse($media as $item)
            @include('admin.media.partials.card', ['media' => $item])
        @empty
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-lg font-medium text-gray-700 mt-2">No media found</p>
                <p class="text-sm text-gray-500 mt-1">Upload files to get started</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($media->hasPages())
        <div class="mt-6">
            {{ $media->links() }}
        </div>
    @endif

<!-- Create Folder Modal -->
<div id="create-folder-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Folder</h3>
            <button class="modal-close" onclick="closeFolderModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="folder-name">Folder Name</label>
                <input type="text" id="folder-name" placeholder="e.g., 2024/01, projects, assets">
                <p class="text-xs text-gray-500 mt-1">Use / to create subfolders (e.g., 2024/01)</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeFolderModal()">Cancel</button>
            <button class="btn btn-primary" onclick="createFolder()">Create Folder</button>
        </div>
    </div>
</div>

<!-- Bulk Rename Modal -->
<div id="bulk-rename-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Rename Selected</h3>
            <button class="modal-close" onclick="closeBulkRenameModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="rename-base">Name base</label>
                <input type="text" id="rename-base" placeholder="e.g. product-photo" maxlength="120">
                <p class="text-xs text-gray-500 mt-1">Files become <code>base.ext</code> (single) or <code>base-1.ext</code>, <code>base-2.ext</code>… Existing files are not overwritten.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeBulkRenameModal()">Cancel</button>
            <button class="btn btn-primary" onclick="executeBulkRename()">Rename</button>
        </div>
    </div>
</div>

<!-- Bulk Alt Text Modal -->
<div id="bulk-alt-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Set Alt Text</h3>
            <button class="modal-close" onclick="closeBulkAltModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="bulk-alt-text">Alt text</label>
                <input type="text" id="bulk-alt-text" placeholder="Describe the image…" maxlength="255">
                <p class="text-xs text-gray-500 mt-1">Applied to all selected <strong>images</strong>. Non-image items are skipped.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeBulkAltModal()">Cancel</button>
            <button class="btn btn-primary" onclick="executeBulkSetAlt()">Apply</button>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div id="confirm-delete-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete <span id="delete-count" class="font-bold text-red-600">0</span> items? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn btn-danger" onclick="executeBulkDelete()">Delete</button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/dropzone@6.0.0-beta.2/dist/dropzone-min.js"></script>
<script>
Dropzone.autoDiscover = false;

// Dropzone Configuration
const dropzone = new Dropzone("#dropzone-form", {
    url: "{{ route('admin.media.store') }}",
    maxFiles: 10,
    maxFilesize: 2,
    acceptedFiles: "image/*,video/*,application/pdf",
    addRemoveLinks: false,
    dictDefaultMessage: "",
    dictFileTooBig: "File too big (max 2MB)",
    init: function() {
        this.on("success", function(file, response) {
            if (response.success) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        });
        this.on("error", function(file, response) {
            alert("Upload failed: " + (response.message || "Unknown error"));
        });
        this.on("addedfile", function(file) {
            if (this.files.length > 10) {
                this.removeFile(file);
                alert("Maximum 10 files at a time");
            }
        });
    }
});

// Handle upload button click
document.getElementById('file-input').addEventListener('change', function(e) {
    if (this.files.length > 0) {
        dropzone.addFiles(Array.from(this.files));
        this.value = ''; // Reset
    }
});

// Handle Enter key in search
function handleEnter(e) {
    if (e.key === 'Enter') {
        applyFilters();
    }
}

// Bulk Selection
let selectedIds = new Set();

function toggleSelect(id, checkbox) {
    if (checkbox.checked) {
        selectedIds.add(id);
    } else {
        selectedIds.delete(id);
    }
    updateBulkActions();
}

function updateBulkActions() {
    const bulkActions = document.getElementById('bulk-actions');
    const countSpan = document.getElementById('selected-count');
    
    if (selectedIds.size > 0) {
        bulkActions.classList.add('show');
        countSpan.textContent = selectedIds.size + ' item' + (selectedIds.size !== 1 ? 's' : '') + ' selected';
    } else {
        bulkActions.classList.remove('show');
    }
}

function deselectAll() {
    document.querySelectorAll('.select-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    selectedIds.clear();
    document.getElementById('bulk-actions').classList.remove('show');
}

function showBulkRenameModal() {
    if (selectedIds.size === 0) return;
    document.getElementById('rename-base').value = '';
    document.getElementById('bulk-rename-modal').classList.add('show');
    setTimeout(() => document.getElementById('rename-base').focus(), 50);
}

function closeBulkRenameModal() {
    document.getElementById('bulk-rename-modal').classList.remove('show');
}

function executeBulkRename() {
    const base = document.getElementById('rename-base').value.trim();
    if (!base) {
        alert('Please enter a name base');
        return;
    }
    if (!/^[A-Za-z0-9_-]+$/.test(base)) {
        alert('Name base may only contain letters, numbers, dashes and underscores');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'rename');
    formData.append('name_base', base);
    selectedIds.forEach(id => formData.append('ids[]', id));

    fetch("{{ route('admin.media.bulk') }}", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeBulkRenameModal();
            deselectAll();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error renaming media');
    });
}

function showBulkAltModal() {
    if (selectedIds.size === 0) return;
    document.getElementById('bulk-alt-text').value = '';
    document.getElementById('bulk-alt-modal').classList.add('show');
    setTimeout(() => document.getElementById('bulk-alt-text').focus(), 50);
}

function closeBulkAltModal() {
    document.getElementById('bulk-alt-modal').classList.remove('show');
}

function executeBulkSetAlt() {
    const alt = document.getElementById('bulk-alt-text').value.trim();
    if (!alt) {
        alert('Please enter alt text');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'set_alt_text');
    formData.append('alt_text', alt);
    selectedIds.forEach(id => formData.append('ids[]', id));

    fetch("{{ route('admin.media.bulk') }}", {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeBulkAltModal();
            deselectAll();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error setting alt text');
    });
}

function bulkDelete() {
    if (selectedIds.size === 0) return;
    
    document.getElementById('delete-count').textContent = selectedIds.size;
    document.getElementById('confirm-delete-modal').classList.add('show');
}

function closeConfirmModal() {
    document.getElementById('confirm-delete-modal').classList.remove('show');
}

function executeBulkDelete() {
    const formData = new FormData();
    formData.append('action', 'delete');
    selectedIds.forEach(id => formData.append('ids[]', id));

    fetch("{{ route('admin.media.bulk') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeConfirmModal();
            deselectAll();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting media');
    });
}

function bulkMoveToFolder() {
    const folderSelect = document.getElementById('bulk-folder-select');
    const folder = folderSelect.value;
    
    if (!folder) {
        alert('Please select a folder first');
        return;
    }

    if (selectedIds.size === 0) return;

    const formData = new FormData();
    formData.append('action', 'move_to_folder');
    formData.append('folder', folder);
    selectedIds.forEach(id => formData.append('ids[]', id));

    fetch("{{ route('admin.media.bulk') }}", {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            deselectAll();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error moving media');
    });
}

// Folder Management
function showCreateFolderModal() {
    document.getElementById('create-folder-modal').classList.add('show');
}

function closeFolderModal() {
    document.getElementById('create-folder-modal').classList.remove('show');
}

function createFolder() {
    const name = document.getElementById('folder-name').value.trim();
    
    if (!name) {
        alert('Please enter a folder name');
        return;
    }

    fetch("{{ route('admin.media.createFolder') }}", {
        method: 'POST',
        body: JSON.stringify({ name: name }),
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeFolderModal();
            document.getElementById('folder-name').value = '';
            
            // Add to folder dropdown
            const folderSelect = document.getElementById('bulk-folder-select');
            const option = document.createElement('option');
            option.value = data.folder;
            option.textContent = data.folder;
            folderSelect.appendChild(option);
        } else {
            alert('Error: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error creating folder');
    });
}

// Filter Functions
function applyFilters() {
    const search = document.getElementById('search-input').value;
    const folder = document.getElementById('folder-filter').value;
    const type = document.getElementById('type-filter').value;
    
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (folder) params.append('folder', folder);
    if (type) params.append('type', type);
    
    const url = '{{ route('admin.media.index') }}' + '?' + params.toString();
    window.location.href = url;
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('folder-filter').value = '';
    document.getElementById('type-filter').value = '';
    window.location.href = '{{ route('admin.media.index') }}';
}

// Handle folder tag clicks
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('folder-link')) {
        e.preventDefault();
        const folder = e.target.dataset.folder;
        document.getElementById('folder-filter').value = folder;
        applyFilters();
    }
});
</script>
@endpush

@endsection
