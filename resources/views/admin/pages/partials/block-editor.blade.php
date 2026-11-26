@php
    $blocks = $post->blocks ?? [];
    $blockTypes = \App\Models\Block::getTypeOptions();
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">Page Builder</h2>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#blockTypeModal">
                <i class="fas fa-plus me-1"></i> Add Block
            </button>
            <button type="button" class="btn btn-sm btn-outline-success" id="save-blocks">
                <i class="fas fa-save me-1"></i> Save Blocks
            </button>
        </div>
    </div>
    <div class="card-body">
        @if(empty($blocks))
            <div class="text-center py-5 text-muted">
                <i class="fas fa-pen-nib fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No blocks yet. Click "Add Block" to start building your page.</p>
            </div>
        @else
            <div x-data="blockEditor()" x-init="initBlocks(@json($blocks))" @keydown.window.escape="closeBlockMenu()" class="block-editor-container">
                <div x-ref="blocks" class="sortable-container">
                    <template x-for="block in blocks" :key="block.id">
                        <div class="block-item card mb-3 border-0 shadow-sm" 
                             :class="{ 'border-primary': selectedBlockId === block.id }">
                            <div class="card-header bg-light py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-grip-vertical text-muted me-2 cursor-grab"></i>
                                        <span class="text-muted small" x-text="getBlockTypeLabel(block.type)"></span>
                                        <span class="badge bg-secondary ms-2" x-text="block.id ? 'Saved' : 'New'"></span>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" @click="toggleBlockMenu(block.id, $event)" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body" @click="selectBlock(block.id)">
                                <div x-show="selectedBlockId !== block.id" class="block-preview" 
                                     x-html="renderBlockPreview(block)"></div>
                                
                                <div x-show="selectedBlockId === block.id" x-transition class="block-edit-form">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Block Content</label>
                                        <template x-if="block.type === 'text'">
                                            <textarea class="form-control form-control-sm" rows="4"
                                                      x-model="block.content.text"
                                                      placeholder="Enter text content..."></textarea>
                                        </template>
                                        
                                        <template x-if="block.type === 'heading'">
                                            <div>
                                                <select class="form-select form-select-sm mb-2" x-model="block.content.level">
                                                    <option value="h1">H1 - Large Heading</option>
                                                    <option value="h2">H2 - Medium Heading</option>
                                                    <option value="h3">H3 - Small Heading</option>
                                                    <option value="h4">H4 - Subheading</option>
                                                    <option value="h5">H5 - Sub-subheading</option>
                                                    <option value="h6">H6 - Smallest</option>
                                                </select>
                                                <input type="text" class="form-control form-control-sm" 
                                                       x-model="block.content.text" placeholder="Heading text...">
                                            </div>
                                        </template>
                                        
                                        <template x-if="block.type === 'image'">
                                            <div>
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control form-control-sm" 
                                                           x-model="block.content.image" placeholder="Image URL or media path">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                            @click="openMediaPicker('block-image-' + block.id)">
                                                        Choose
                                                    </button>
                                                </div>
                                                <input type="text" class="form-control form-control-sm mb-2"
                                                       x-model="block.content.caption" placeholder="Caption (optional)">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           x-model="block.content.showCaption" id="caption-{{ $id() }}">
                                                    <label class="form-check-label small" for="caption-{{ $id() }}">
                                                        Show caption below image
                                                    </label>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <template x-if="block.type === 'callout'">
                                            <div>
                                                <textarea class="form-control form-control-sm mb-2" rows="3"
                                                          x-model="block.content.text" 
                                                          placeholder="Callout message..."></textarea>
                                                <div class="row g-2">
                                                    <div class="col">
                                                        <select class="form-select form-select-sm" x-model="block.content.style">
                                                            <option value="primary">Primary</option>
                                                            <option value="secondary">Secondary</option>
                                                            <option value="success">Success</option>
                                                            <option value="danger">Danger</option>
                                                            <option value="warning">Warning</option>
                                                            <option value="info">Info</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <input type="text" class="form-control form-control-sm" 
                                                               x-model="block.content.icon" placeholder="Icon (optional)">
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <template x-if="block.type === 'divider'">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="divider-style-{{ $id() }}" 
                                                           x-model="block.content.style" value="solid" id="solid-{{ $id() }}">
                                                    <label class="form-check-label small" for="solid-{{ $id() }}">Solid</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="divider-style-{{ $id() }}" 
                                                           x-model="block.content.style" value="dashed" id="dashed-{{ $id() }}">
                                                    <label class="form-check-label small" for="dashed-{{ $id() }}">Dashed</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="divider-style-{{ $id() }}" 
                                                           x-model="block.content.style" value="dotted" id="dotted-{{ $id() }}">
                                                    <label class="form-check-label small" for="dotted-{{ $id() }}">Dotted</label>
                                                </div>
                                                <div class="vr"></div>
                                                <input type="range" class="form-range w-50" min="1" max="10" 
                                                       x-model="block.content.thickness" id="dividerThickness">
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light py-2 small text-muted d-flex justify-content-between">
                                <span x-text="'Order: ' + block.order"></span>
                                <div class="btn-group btn-group-xs">
                                    <button type="button" class="btn btn-outline-secondary" 
                                            @click="moveBlock(block.id, 'up')" :disabled="block.order === 1">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" 
                                            @click="moveBlock(block.id, 'down')" :disabled="block.order === blocks.length">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            @click="deleteBlock(block.id)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Block Type Modal -->
<div class="modal fade" id="blockTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Block</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="block-type-grid">
                    <template x-for="[value, label] in Object.entries(@js($blockTypes))" :key="value">
                        <div class="col-6 col-md-4">
                            <button type="button" class="btn btn-outline-secondary w-100 text-start p-3" 
                                    @click="addBlock(value)" data-bs-dismiss="modal">
                                <i class="fas fa-plus-circle text-primary me-2"></i>
                                <span x-text="label"></span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Media Picker Modal -->
<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPickerModalLabel">Choose Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="media-picker-search" class="form-control mb-3"
                       placeholder="Search images by name or alt text">
                <div id="media-picker-grid" class="row g-3">
                    <div class="col-12 text-muted small">Loading images…</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .block-editor-container { position: relative; }
    .block-item { transition: all 0.2s ease; }
    .block-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .block-item .cursor-grab { cursor: grab; }
    .block-item:active .cursor-grab { cursor: grabbing; }
    .sortable-container { position: relative; }
    .sortable-placeholder { 
        border: 2px dashed #dee2e6; 
        border-radius: 0.5rem; 
        min-height: 100px;
        background: #f8f9fa;
    }
    .block-preview { padding: 1rem; }
    .block-preview img { max-width: 100%; height: auto; border-radius: 0.375rem; }
    .callout-primary { background: #0d6efd20; border-left: 4px solid #0d6efd; padding: 1rem; }
    .callout-secondary { background: #6c757d20; border-left: 4px solid #6c757d; padding: 1rem; }
    .callout-success { background: #19875420; border-left: 4px solid #198754; padding: 1rem; }
    .callout-danger { background: #dc354520; border-left: 4px solid #dc3545; padding: 1rem; }
    .callout-warning { background: #ffc10720; border-left: 4px solid #ffc107; padding: 1rem; }
    .callout-info { background: #0dcaf020; border-left: 4px solid #0dcaf0; padding: 1rem; }
    .divider-solid { border-top: 1px solid #dee2e6; }
    .divider-dashed { border-top: 2px dashed #dee2e6; }
    .divider-dotted { border-top: 1px dotted #dee2e6; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('blockEditor', () => ({
        blocks: [],
        selectedBlockId: null,
        blockTypeModal: null,
        mediaPickerTarget: null,
        
        initBlocks(blocks) {
            this.blocks = blocks.map((block, index) => ({
                ...block,
                order: block.order || index + 1
            }));
            this.setupDragAndDrop();
        },
        
        getBlockTypeLabel(type) {
            const labels = {
                'text': 'Text Block',
                'heading': 'Heading',
                'image': 'Image',
                'gallery': 'Gallery',
                'callout': 'Callout',
                'grid': 'Grid',
                'quote': 'Quote',
                'divider': 'Divider',
                'html': 'HTML'
            };
            return labels[type] || type;
        },
        
        addBlock(type) {
            const settings = {
                text: { alignment: 'left', margin: 'md' },
                heading: { level: 'h2', alignment: 'left', margin: 'md' },
                image: { alignment: 'left', caption: '' },
                gallery: { columns: 3, gap: 'md' },
                callout: { style: 'primary', icon: '' },
                grid: { columns: 2, gap: 'md' },
                quote: { alignment: 'center', style: 'default' },
                divider: { style: 'solid', thickness: 1 },
                html: {}
            };
            
            const newBlock = {
                id: null,
                type: type,
                content: {},
                settings: settings[type] || {},
                order: this.blocks.length + 1,
                isNew: true
            };
            
            if (type === 'text') newBlock.content = { text: '' };
            if (type === 'heading') newBlock.content = { level: 'h2', text: 'New Heading' };
            if (type === 'image') newBlock.content = { image: '', caption: '' };
            if (type === 'callout') newBlock.content = { text: '', style: 'primary', icon: '' };
            if (type === 'divider') newBlock.content = { style: 'solid', thickness: 1 };
            
            this.blocks.push(newBlock);
            this.selectBlock(newBlock.id);
        },
        
        selectBlock(id) {
            this.selectedBlockId = id;
        },
        
        closeBlockMenu() {
            this.selectedBlockId = null;
        },
        
        toggleBlockMenu(id, event) {
            event.stopPropagation();
            this.selectedBlockId = this.selectedBlockId === id ? null : id;
        },
        
        renderBlockPreview(block) {
            if (block.type === 'text') {
                return `<div class="text-muted"><i class="fas fa-paragraph me-1"></i>${(block.content.text || '').substring(0, 100)}${block.content.text && block.content.text.length > 100 ? '...' : ''}</div>`;
            }
            if (block.type === 'heading') {
                return `<${block.content.level || 'h2'} class="text-primary">Heading Preview</${block.content.level || 'h2'}>`;
            }
            if (block.type === 'image') {
                return block.content.image 
                    ? `<img src="${block.content.image}" alt="Image" class="rounded" style="max-height:150px">`
                    : '<div class="text-muted"><i class="fas fa-image me-1"></i>No image selected</div>';
            }
            if (block.type === 'callout') {
                return `<div class="callout-${block.content.style || 'primary'}">${(block.content.text || '').substring(0, 50)}...</div>`;
            }
            if (block.type === 'divider') {
                const style = block.content.style === 'dashed' ? 'dashed' : block.content.style === 'dotted' ? 'dotted' : 'solid';
                return `<div class="divider-${style}" style="border-top-width:${block.content.thickness || 1}px"></div>`;
            }
            return `<div class="text-muted"><i class="fas fa-cube me-1"></i>${this.getBlockTypeLabel(block.type)}</div>`;
        },
        
        moveBlock(id, direction) {
            const index = this.blocks.findIndex(b => b.id === id);
            if (index === -1) return;
            
            if (direction === 'up' && index > 0) {
                [this.blocks[index], this.blocks[index - 1]] = [this.blocks[index - 1], this.blocks[index]];
                this.updateBlockOrders();
            } else if (direction === 'down' && index < this.blocks.length - 1) {
                [this.blocks[index], this.blocks[index + 1]] = [this.blocks[index + 1], this.blocks[index]];
                this.updateBlockOrders();
            }
        },
        
        deleteBlock(id) {
            if (!confirm('Are you sure you want to delete this block?')) return;
            
            this.blocks = this.blocks.filter(b => b.id !== id);
            this.updateBlockOrders();
            this.selectedBlockId = null;
        },
        
        updateBlockOrders() {
            this.blocks.forEach((block, index) => {
                block.order = index + 1;
            });
        },
        
        saveBlocks() {
            const orderedBlocks = this.blocks.map(b => ({
                id: b.id,
                type: b.type,
                content: b.content,
                settings: b.settings,
                order: b.order
            }));
            
            fetch('{{ route("admin.pages.blocks.reorder", $post->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]')?.content || 
                                   document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ order: orderedBlocks.map(b => b.id) })
            })
            .then(response => response.json())
            .then(() => {
                // Save individual blocks
                this.blocks.forEach(block => {
                    const action = block.id 
                        ? `{{ route('admin.pages.blocks.update', [$post->id, ':id']) }}`.replace(':id', block.id)
                        : `{{ route('admin.pages.blocks.store', $post->id) }}`;
                    
                    fetch(action, {
                        method: block.id ? 'PUT' : 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]')?.content ||
                                          document.querySelector('meta[name="csrf-token"]')?.content
                        },
                        body: JSON.stringify({
                            type: block.type,
                            content: block.content,
                            settings: block.settings,
                            order: block.order
                        })
                    })
                    .then(response => response.json())
                    .then(savedBlock => {
                        if (!block.id) {
                            this.blocks = this.blocks.map(b => 
                                b.id === null ? savedBlock : b
                            );
                        }
                    });
                });
                
                alert('Blocks saved successfully!');
                location.reload();
            })
            .catch(err => console.error('Save error:', err));
        },
        
        setupDragAndDrop() {
            const container = this.$refs.blocks;
            if (!container) return;
            
            let draggedItem = null;
            
            container.addEventListener('dragstart', (e) => {
                draggedItem = e.target.closest('.block-item');
                e.target.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
            });
            
            container.addEventListener('dragend', (e) => {
                e.target.classList.remove('opacity-50');
                draggedItem = null;
            });
            
            container.addEventListener('dragover', (e) => {
                e.preventDefault();
                const afterElement = this.getDragAfterElement(container, e.clientY);
                const draggable = document.querySelector('.block-item.opacity-50');
                if (afterElement == null) {
                    container.appendChild(draggable);
                } else {
                    container.insertBefore(draggable, afterElement);
                }
            });
            
            container.addEventListener('drop', () => {
                this.updateBlockOrders();
            });
        },
        
        getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.block-item:not(.opacity-50)')];
            
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        },
        
        openMediaPicker(target) {
            this.mediaPickerTarget = target;
            const modal = new bootstrap.Modal(document.getElementById('mediaPickerModal'));
            modal.show();
            
            // Load media
            fetch('{{ route("admin.pages.blocks.media-picker", $post->id) }}', {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(media => {
                const grid = document.getElementById('media-picker-grid');
                grid.innerHTML = media.map(item => `
                    <div class="col-6 col-md-4">
                        <button type="button" class="btn p-0 border rounded w-100" 
                                onclick="selectMedia('${item.path || item.url}', '${(item.alt || '').replace(/'/g, "\\'")}')">
                            <img src="${item.url}" class="img-fluid rounded-top" style="height:100px;width:100%;object-fit:cover">
                            <span class="d-block small text-truncate px-2 py-1">${item.name}</span>
                        </button>
                    </div>
                `).join('');
            });
        }
    }));
});

// Global media picker handler
window.selectMedia = function(url, alt) {
    const blockId = Alpine.$data(document.querySelector('[x-data]'));
    if (blockId && blockId.mediaPickerTarget) {
        const input = document.querySelector('#' + blockId.mediaPickerTarget);
        if (input) {
            input.value = url;
        }
    }
    bootstrap.Modal.getOrCreateInstance(document.getElementById('mediaPickerModal')).hide();
};
</script>
@endpush
