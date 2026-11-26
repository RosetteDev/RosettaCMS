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
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleBlockManager()">
                        <i class="fas fa-list me-1"></i>Blocks
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="saveInlineContent()">
                        <i class="fas fa-save me-1"></i>Save Changes
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
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize drag and drop
    const editor = document.getElementById('inlineContentEditor');
    if (editor) {
        editor.addEventListener('dragstart', handleDragStart);
        editor.addEventListener('dragover', handleDragOver);
        editor.addEventListener('drop', handleDrop);
        editor.addEventListener('dragend', handleDragEnd);
    }

    window.toggleBlockManager = function() {
        // Toggle block manager sidebar
        const manager = document.getElementById('blockManager');
        if (manager) {
            manager.classList.toggle('show');
        }
    };

    window.saveInlineContent = function() {
        const pageId = {{ $page->id ?? 0 }}; // route /inline-edit/{post}/blocks/{block}
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
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
            alert('Content saved successfully!');
            closeInlineEdit();
            location.reload();
        })
        .catch(function(err) {
            console.error('Save error:', err);
            alert('Failed to save changes: ' + err.message);
        });
    };

    function handleDragStart(e) {
        e.dataTransfer.setData('text/plain', e.target.dataset.blockId);
        e.target.classList.add('dragging');
        setTimeout(() => e.target.classList.add('opacity-50'), 0);
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDrop(e) {
        e.preventDefault();
        const blockId = e.dataTransfer.getData('text/plain');
        const draggedEl = document.querySelector('.block-item[data-block-id="' + blockId + '"]');
        const targetEl = e.target.closest('.block-item');

        if (draggedEl && targetEl && draggedEl !== targetEl) {
            const blocks = document.querySelectorAll('.block-item');
            const targetIndex = Array.from(blocks).indexOf(targetEl);
            const draggedIndex = Array.from(blocks).indexOf(draggedEl);

            if (targetIndex > draggedIndex) {
                targetEl.after(draggedEl);
            } else {
                targetEl.before(draggedEl);
            }
        }

        if (draggedEl) {
            draggedEl.classList.remove('dragging', 'opacity-50');
        }
    }

    function handleDragEnd(e) {
        e.target.classList.remove('dragging', 'opacity-50');
    }
});
</script>
@endpush
