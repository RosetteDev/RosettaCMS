<!-- Inline Edit Modal -->
<div id="inlineEditModal" class="rosetta-inline-edit-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:9999;overflow-y:auto;">
    <div style="max-width:900px;margin:2rem auto;padding:0 1.5rem;">
        <!-- Toolbar -->
        <div style="background:#fff;padding:1rem;border-radius:0.5rem;margin-bottom:1.5rem;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0"><i class="fas fa-pen-nib me-2 text-primary"></i>Inline Edit</h4>
                    <small class="text-muted">Edit this page content directly</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <span id="inlineEditStatus" class="small text-muted me-1"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleBlockManager()">
                        <i class="fas fa-list me-1"></i>Blocks
                    </button>
                    <button type="button" id="inlineEditSaveBtn" class="btn btn-sm btn-success" onclick="saveInlineContent()" disabled>
                        <i class="fas fa-spinner fa-spin me-1" id="inlineEditSaveSpinner" style="display:none"></i>
                        <i class="fas fa-save me-1" id="inlineEditSaveIcon"></i><span id="inlineEditSaveLabel">Save Changes</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeInlineEdit()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Inline Content Editor -->
        <div id="inlineContentEditor" style="background:#fff;border-radius:0.5rem;overflow:hidden;">
            @if($page && $page->blocks && $page->blocks->isNotEmpty())
                <div class="p-4">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Page Builder Mode:</strong> Drag blocks to reorder, click to edit content.
                    </div>
                    @foreach($page->blocks as $block)
                        @include('themes.rosetta.partials.inline-block-editor', ['block' => $block])
                    @endforeach
                </div>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-pen-nib fa-3x mb-3 opacity-50"></i>
                    <p>No blocks found. Create blocks in the admin panel to use the page builder.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.rosetta-inline-edit-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; overflow-y: auto; }
.rosetta-inline-edit-modal .block-editor { position: relative; }
.rosetta-inline-edit-modal .block-item { transition: all 0.2s; }
.rosetta-inline-edit-modal .block-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.rosetta-inline-edit-modal .block-item:hover .block-controls { display: flex !important; }
.rosetta-inline-edit-modal .block-controls { display: none; position: absolute; top: 0.5rem; right: 0.5rem; gap: 0.25rem; }
.rosetta-inline-edit-modal .block-controls button { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.rosetta-inline-edit-modal .block-content-editable { outline: none; padding: 1rem; min-height: 2rem; }
.rosetta-inline-edit-modal .block-content-editable:focus { background: #f8f9fa; border-radius: 0.375rem; }
.rosetta-inline-edit-modal .block-type-label { position: absolute; top: 0.5rem; left: 0.5rem; font-size: 0.75rem; background: rgba(255,255,255,0.9); padding: 0.25rem 0.5rem; border-radius: 0.25rem; pointer-events: none; }
.rosetta-inline-edit-modal .sortable-placeholder { border: 2px dashed #dee2e6; border-radius: 0.375rem; min-height: 100px; }
.rosetta-inline-edit-modal .block-item.dragging { opacity: 0.45; }
.rosetta-inline-edit-modal .block-item.drop-target { outline: 2px dashed #3b82f6; outline-offset: 4px; }
.rosetta-inline-edit-modal .block-item { position: relative; margin-bottom: 1rem; border-radius: 0.5rem; overflow: hidden; background: #fff; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editor = document.getElementById('inlineContentEditor');
    const pageId = {{ $page->id ?? 0 }};
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const saveBtn = document.getElementById('inlineEditSaveBtn');
    const saveSpinner = document.getElementById('inlineEditSaveSpinner');
    const saveIcon = document.getElementById('inlineEditSaveIcon');
    const saveLabel = document.getElementById('inlineEditSaveLabel');
    const statusEl = document.getElementById('inlineEditStatus');

    let dirty = false;
    let saving = false;

    function markDirty() {
        dirty = true;
        if (saveBtn) saveBtn.disabled = false;
        if (statusEl) statusEl.textContent = 'Unsaved changes';
    }

    function resetDirty() {
        dirty = false;
        if (saveBtn) saveBtn.disabled = true;
        if (statusEl) statusEl.textContent = '';
    }

    function setSaving(state) {
        saving = state;
        if (saveBtn) saveBtn.disabled = state;
        if (saveSpinner) saveSpinner.style.display = state ? '' : 'none';
        if (saveIcon) saveIcon.style.display = state ? 'none' : '';
        if (saveLabel) saveLabel.textContent = state ? 'Saving…' : 'Save Changes';
    }

    function setStatus(text, isError) {
        if (!statusEl) return;
        statusEl.textContent = text;
        statusEl.className = 'small me-1 ' + (isError ? 'text-danger' : 'text-success');
    }

    // Initialize drag and drop + live dirty tracking
    if (editor) {
        editor.addEventListener('dragstart', handleDragStart);
        editor.addEventListener('dragover', handleDragOver);
        editor.addEventListener('drop', handleDrop);
        editor.addEventListener('dragend', handleDragEnd);
        editor.addEventListener('input', markDirty);
    }

    window.toggleBlockManager = function() {
        const manager = document.getElementById('blockManager');
        if (manager) {
            manager.classList.toggle('show');
        }
    };

    // ----- block operations -----
    window.moveBlock = function(blockId, direction) {
        const item = document.querySelector('.block-item[data-block-id="' + blockId + '"]');
        if (!item) return;
        const sibling = direction === 'up' ? item.previousElementSibling : item.nextElementSibling;
        if (!sibling) return;
        if (direction === 'up') {
            sibling.before(item);
        } else {
            sibling.after(item);
        }
        markDirty();
    };

    window.deleteBlock = function(blockId) {
        const item = document.querySelector('.block-item[data-block-id="' + blockId + '"]');
        if (!item) return;
        if (!confirm('Delete this block? This cannot be undone.')) return;

        fetch('/inline-edit/' + pageId + '/blocks/' + blockId, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(function(r) {
            if (!r.ok) throw new Error('Delete failed');
            item.remove();
            markDirty();
            setStatus('Block deleted', false);
        }).catch(function(err) {
            alert('Failed to delete block: ' + err.message);
        });
    };

    window.saveInlineContent = function() {
        if (saving) return;
        const blockElements = document.querySelectorAll('.block-item');
        const updates = [];
        const orderedIds = [];

        blockElements.forEach(function(el) {
            const blockId = el.dataset.blockId;
            const type = el.dataset.blockType;

            let content = {};
            try {
                content = JSON.parse(el.dataset.blockContent || '{}');
            } catch (e) {
                content = {};
            }

            const editable = el.querySelector('.block-content-editable');
            if (editable && editable.getAttribute('contenteditable') === 'true') {
                const text = (editable.innerText || '').trim();
                if (['text', 'heading', 'callout', 'quote'].indexOf(type) !== -1) {
                    content.text = text;
                }
            }

            updates.push({ id: blockId, content: content });
            orderedIds.push(blockId);
        });

        if (updates.length === 0) {
            alert('No blocks to save');
            return;
        }

        setSaving(true);
        setStatus('', false);

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        };

        // Persist each block's content, then persist the new order.
        Promise.all(updates.map(function(u) {
            return fetch('/inline-edit/' + pageId + '/blocks/' + u.id, {
                method: 'PUT',
                headers: headers,
                body: JSON.stringify({ content: u.content })
            }).then(function(r) {
                if (!r.ok) throw new Error('Failed to save block ' + u.id);
                return r;
            });
        }))
        .then(function() {
            return fetch('/admin/pages/' + pageId + '/blocks/reorder', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({ order: orderedIds })
            });
        })
        .then(function(r) {
            if (!r.ok) throw new Error('Failed to save block order');
            setSaving(false);
            resetDirty();
            setStatus('Saved ✓', false);
            // Reload so the frontend reflects the persisted blocks.
            setTimeout(function() { window.location.reload(); }, 600);
        })
        .catch(function(err) {
            setSaving(false);
            console.error('Save error:', err);
            setStatus('Save failed', true);
            alert('Failed to save changes: ' + err.message);
        });
    };

    function handleDragStart(e) {
        const item = e.target.closest('.block-item');
        if (!item) return;
        e.dataTransfer.setData('text/plain', item.dataset.blockId);
        e.dataTransfer.effectAllowed = 'move';
        item.classList.add('dragging');
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        const item = e.target.closest('.block-item');
        if (item) {
            item.classList.add('drop-target');
        }
    }

    function handleDrop(e) {
        e.preventDefault();
        const blockId = e.dataTransfer.getData('text/plain');
        const draggedEl = document.querySelector('.block-item[data-block-id="' + blockId + '"]');
        const targetEl = e.target.closest('.block-item');

        document.querySelectorAll('.drop-target').forEach(function(el) { el.classList.remove('drop-target'); });

        if (draggedEl && targetEl && draggedEl !== targetEl) {
            const blocks = document.querySelectorAll('.block-item');
            const targetIndex = Array.from(blocks).indexOf(targetEl);
            const draggedIndex = Array.from(blocks).indexOf(draggedEl);

            if (targetIndex > draggedIndex) {
                targetEl.after(draggedEl);
            } else {
                targetEl.before(draggedEl);
            }
            markDirty();
        }
    }

    function handleDragEnd(e) {
        const item = e.target.closest('.block-item');
        if (item) item.classList.remove('dragging');
        document.querySelectorAll('.drop-target').forEach(function(el) { el.classList.remove('drop-target'); });
    }
});
</script>
@endpush
