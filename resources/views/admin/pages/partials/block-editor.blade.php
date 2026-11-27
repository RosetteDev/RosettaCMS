@php
    // Top-level blocks only (nested children of container blocks render inside
    // their parent). The editor operates on the flat set, with nested children
    // carried on the container block.
    $blocks = $post->blocks()->with('childBlocks')->whereNull('parent_block_id')->get()->values();
    $blockTypes = \App\Models\Block::getTypeOptions();
    $blocksPayload = $blocks->map(fn($b) => [
        'id' => $b->id,
        'type' => $b->type,
        'content' => $b->content ?? [],
        'settings' => $b->settings ?? [],
        'order' => $b->order,
        'children' => $b->childBlocks->map(fn($c) => [
            'id' => $c->id,
            'type' => $c->type,
            'content' => $c->content ?? [],
            'settings' => $c->settings ?? [],
            'order' => $c->order,
        ])->values()->all(),
    ])->values();
    $reusableBlocks = \App\Models\ReusableBlock::with('site')->latest()->get();
    $reusableBlocksPayload = $reusableBlocks->map(fn($r) => [
        'id' => $r->id,
        'name' => $r->name,
        'description' => $r->description,
        'blocks' => $r->blocks(),
    ])->values();
    $editorConfig = [
        'blocks' => $blocksPayload,
        'blockTypes' => $blockTypes,
        'patterns' => \App\Support\BlockPatterns::all(),
        'reusableBlocks' => $reusableBlocksPayload,
        'urls' => [
            'store' => route('admin.pages.blocks.store', $post->id),
            'reorder' => route('admin.pages.blocks.reorder', $post->id),
            'mediaPicker' => route('admin.pages.blocks.media-picker', $post->id),
            'preview' => route('preview.update', $post->id),
            'previewPage' => route('preview.show', $post->id),
            'update' => route('admin.pages.blocks.update', [$post->id, '__ID__']),
            'destroy' => route('admin.pages.blocks.destroy', [$post->id, '__ID__']),
            'reusableStore' => route('admin.reusable-blocks.store'),
        ],
    ];
@endphp

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent">
        <h2 class="h5 mb-0">Page Builder</h2>
    </div>
    <div class="card-body">
        <div x-data="blockEditor()" x-init="init()" @keydown.window="handleKeydown($event)" @keydown.window.escape="closeBlockMenu()" class="block-editor-container">

            <div class="editor-toolbar mb-3 d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-primary" @click="openAddModal()">
                    <i class="fas fa-plus me-1"></i> Add Block
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="undo()" :disabled="!canUndo" title="Undo (Ctrl+Z)">
                    <i class="fas fa-rotate-left"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="redo()" :disabled="!canRedo" title="Redo (Ctrl+Shift+Z)">
                    <i class="fas fa-rotate-right"></i>
                </button>
                <span class="badge bg-success" x-show="savedMessage" x-text="savedMessage"></span>
                <span class="badge bg-danger" x-show="saveError" x-text="saveError"></span>
                <button type="button" class="btn btn-sm btn-outline-secondary" x-show="selectedBlock()" @click="openSaveReusableModal()" title="Save the selected block as a reusable template">
                    <i class="fas fa-copy me-1"></i> Save as Reusable
                </button>
                <span class="flex-grow-1"></span>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="openPreview()">
                    <i class="fas fa-eye me-1"></i> Preview
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="openPreviewOnSite()" title="Open the live preview on the frontend">
                    <i class="fas fa-external-link-alt me-1"></i> Open on Site
                </button>
                <button type="button" class="btn btn-sm btn-success" @click="save()" :disabled="saving">
                    <i class="fas fa-spinner fa-spin me-1" x-show="saving"></i>
                    <i class="fas fa-save me-1" x-show="!saving"></i>
                    <span x-text="saving ? 'Saving…' : 'Save Blocks'"></span>
                </button>
            </div>

            <div x-show="blocks.length === 0" class="text-center py-5 text-muted">
                <i class="fas fa-pen-nib fa-3x mb-3 opacity-50"></i>
                <p class="mb-3">No blocks yet. Click "Add Block" to start building your page.</p>
                <button type="button" class="btn btn-primary btn-sm" @click="openAddModal()">
                    <i class="fas fa-plus me-1"></i> Add Block
                </button>
            </div>

            <div x-ref="container" class="sortable-container" x-show="blocks.length > 0">
                <template x-for="block in blocks" :key="block.tempId">
                    <div class="block-item card mb-3 border-0 shadow-sm"
                         draggable="true"
                         tabindex="0"
                         :aria-label="'Block: ' + getBlockTypeLabel(block.type) + (selectedBlockId === block.tempId ? ' (selected)' : '')"
                         :data-temp-id="block.tempId"
                         :class="{ 'border-primary': selectedBlockId === block.tempId }"
                         @dragstart="onDragStart($event)"
                         @dragover="onDragOver($event)"
                         @drop="onDrop($event)"
                         @dragend="onDragEnd()"
                         @keydown.enter.prevent="selectBlock(block.tempId); focusSelectedBlock()">
                        <div class="card-header bg-light py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-grip-vertical text-muted me-2 cursor-grab"></i>
                                    <span class="text-muted small" x-text="getBlockTypeLabel(block.type)"></span>
                                    <span class="badge bg-secondary ms-2" x-text="block.id ? 'Saved' : 'New'"></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="toggleBlockMenu(block.tempId)" :aria-label="'Block options for ' + getBlockTypeLabel(block.type)">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" @click="selectBlock(block.tempId)">
                            <div x-show="selectedBlockId !== block.tempId" class="block-preview" x-html="renderBlockPreview(block)"></div>
                            <div x-show="selectedBlockId === block.tempId" x-transition class="block-edit-form">
                                <template x-if="block.type === 'text'">
                                    <textarea class="form-control form-control-sm" rows="4"
                                              x-model="block.content.text"
                                              @focus="onFieldFocus()" @change="onFieldChange()"
                                              placeholder="Enter text content..."></textarea>
                                </template>
                                <template x-if="block.type === 'heading'">
                                    <div>
                                        <select class="form-select form-select-sm mb-2" @focus="onFieldFocus()" x-model="block.content.level" @change="onFieldChange()">
                                            <option value="h1">H1 - Large Heading</option>
                                            <option value="h2">H2 - Medium Heading</option>
                                            <option value="h3">H3 - Small Heading</option>
                                            <option value="h4">H4 - Subheading</option>
                                            <option value="h5">H5 - Sub-subheading</option>
                                            <option value="h6">H6 - Smallest</option>
                                        </select>
                                        <input type="text" class="form-control form-control-sm"
                                               x-model="block.content.text" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="Heading text...">
                                    </div>
                                </template>
                                <template x-if="block.type === 'image'">
                                    <div>
                                        <div class="input-group mb-2">
                                            <input type="text" class="form-control form-control-sm"
                                                   x-model="block.content.image" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="Image URL or media path">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="openMediaPicker(block.tempId, 'image')">
                                                Choose
                                            </button>
                                        </div>
                                        <input type="text" class="form-control form-control-sm mb-2"
                                               x-model="block.content.caption" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="Caption (optional)">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" @focus="onFieldFocus()" x-model="block.content.showCaption" @change="onFieldChange()" x-bind:id="'showCaption-' + block.tempId">
                                            <label class="form-check-label small" x-bind:for="'showCaption-' + block.tempId">
                                                Show caption below image
                                            </label>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="block.type === 'callout'">
                                    <div>
                                        <textarea class="form-control form-control-sm mb-2" rows="3"
                                                  x-model="block.content.text" @focus="onFieldFocus()" @change="onFieldChange()"
                                                  placeholder="Callout message..."></textarea>
                                        <div class="row g-2">
                                            <div class="col">
                                                <select class="form-select form-select-sm" @focus="onFieldFocus()" x-model="block.content.style" @change="onFieldChange()">
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
                                                       x-model="block.content.icon" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="Icon (optional)">
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="block.type === 'divider'">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" @focus="onFieldFocus()" x-model="block.content.style" @change="onFieldChange()" value="solid" x-bind:name="'divider-style-' + block.tempId" x-bind:id="'solid-' + block.tempId">
                                            <label class="form-check-label small" x-bind:for="'solid-' + block.tempId">Solid</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" @focus="onFieldFocus()" x-model="block.content.style" @change="onFieldChange()" value="dashed" x-bind:name="'divider-style-' + block.tempId" x-bind:id="'dashed-' + block.tempId">
                                            <label class="form-check-label small" x-bind:for="'dashed-' + block.tempId">Dashed</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" @focus="onFieldFocus()" x-model="block.content.style" @change="onFieldChange()" value="dotted" x-bind:name="'divider-style-' + block.tempId" x-bind:id="'dotted-' + block.tempId">
                                            <label class="form-check-label small" x-bind:for="'dotted-' + block.tempId">Dotted</label>
                                        </div>
                                        <div class="vr"></div>
                                        <input type="range" class="form-range w-50" min="1" max="10"
                                               @focus="onFieldFocus()" x-model="block.content.thickness" @change="onFieldChange()">
                                    </div>
                                </template>
                                <template x-if="block.type === 'quote'">
                                    <div>
                                        <textarea class="form-control form-control-sm mb-2" rows="3"
                                                  x-model="block.content.text" @focus="onFieldFocus()" @change="onFieldChange()"
                                                  placeholder="Quote text..."></textarea>
                                        <input type="text" class="form-control form-control-sm"
                                               x-model="block.content.cite" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="— Citation (optional)">
                                    </div>
                                </template>
                                <template x-if="block.type === 'button'">
                                    <div class="d-grid gap-2">
                                        <input type="text" class="form-control form-control-sm" x-model="block.content.text" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="Button text...">
                                        <input type="text" class="form-control form-control-sm" x-model="block.content.url" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="Link URL...">
                                        <select class="form-select form-select-sm" @focus="onFieldFocus()" x-model="block.content.style" @change="onFieldChange()">
                                            <option value="primary">Primary</option>
                                            <option value="secondary">Secondary</option>
                                            <option value="success">Success</option>
                                            <option value="danger">Danger</option>
                                        </select>
                                    </div>
                                </template>
                                <template x-if="block.type === 'spacer'">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted">Height</span>
                                        <input type="range" class="form-range" min="10" max="200"
                                               @focus="onFieldFocus()" x-model="block.content.size" @change="onFieldChange()">
                                        <span class="small" x-text="(block.content.size ?? 40) + 'px'"></span>
                                    </div>
                                </template>
                                <template x-if="block.type === 'video'">
                                    <div class="d-grid gap-2">
                                        <input type="text" class="form-control form-control-sm" x-model="block.content.url" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="Video URL (YouTube, Vimeo, or direct file)">
                                        <input type="text" class="form-control form-control-sm" x-model="block.content.caption" @focus="onFieldFocus()" @change="onFieldChange()" placeholder="Caption (optional)">
                                    </div>
                                </template>
                                <template x-if="block.type === 'html'">
                                    <textarea class="form-control form-control-sm font-monospace" rows="6"
                                              x-model="block.content.html" @focus="onFieldFocus()" @change="onFieldChange()"
                                              placeholder="<div>Custom HTML</div>"></textarea>
                                </template>
                                <template x-if="block.type === 'gallery'">
                                    <div>
                                        <textarea class="form-control form-control-sm mb-2" rows="4"
                                                  x-model="block.content.imagesText" @focus="onFieldFocus()" @change="onFieldChange()"
                                                  placeholder="One image URL per line..."></textarea>
                                        <small class="text-muted">One image URL per line</small>
                                        <div class="d-flex gap-3 mt-2 flex-wrap">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="small text-muted">Layout</span>
                                                <select class="form-select form-select-sm" style="width:auto" @focus="onFieldFocus()" x-model="block.content.layout" @change="onFieldChange()">
                                                    <option value="grid">Grid</option>
                                                    <option value="carousel">Carousel</option>
                                                </select>
                                            </div>
                                            <template x-if="(block.content.layout || 'grid') === 'grid'">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="small text-muted">Columns</span>
                                                    <select class="form-select form-select-sm" style="width:auto" @focus="onFieldFocus()" x-model="block.content.columns" @change="onFieldChange()">
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                    </select>
                                                </div>
                                            </template>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="small text-muted">Gap</span>
                                                <input type="range" class="form-range" min="0" max="40" @focus="onFieldFocus()" x-model="block.content.gap" @change="onFieldChange()">
                                                <span class="small" x-text="(block.content.gap ?? 10) + 'px'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="block.type === 'columns'">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="small text-muted">Columns</span>
                                            <select class="form-select form-select-sm" style="width:auto" @focus="onFieldFocus()" x-model="block.content.columns" @change="onFieldChange()">
                                                <option value="1">1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="small text-muted">Gutter</span>
                                            <input type="range" class="form-range" min="0" max="40" @focus="onFieldFocus()" x-model="block.content.gutter" @change="onFieldChange()">
                                            <span class="small" x-text="(block.content.gutter ?? 20) + 'px'"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!['text','heading','image','callout','divider','quote','button','spacer','video','html','gallery','columns'].includes(block.type)">
                                    <div class="text-muted small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This block type has no dedicated editor. It will be saved and rendered as-is.
                                    </div>
                                </template>
                            </div>
                        </div>
                        <template x-if="block.type === 'columns'">
                            <div class="nested-zone"
                                 @dragover.prevent.stop="onNestedDragOver($event, block.tempId)"
                                 @dragleave.stop="onNestedDragLeave()"
                                 @drop.prevent.stop="onNestedDrop($event, block.tempId)"
                                 :class="{ 'nested-zone-active': nestedZoneActive === block.tempId }">
                                <div class="nested-zone-header">
                                    <span class="small text-uppercase text-muted" style="font-size: 0.65rem; letter-spacing: 0.06em;">Nested blocks</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click="openAddModal(block.tempId)" title="Add a block inside this container">
                                        <i class="fas fa-plus me-1"></i> Add
                                    </button>
                                </div>
                                <template x-for="child in block.children" :key="child.tempId">
                                    <div class="nested-child-item"
                                         draggable="true"
                                         :data-temp-id="child.tempId"
                                         @dragstart.stop="onChildDragStart($event, block.tempId)"
                                         @dragend="onDragEnd()"
                                         @click="selectChild(block.tempId, child.tempId)"
                                         :class="{ 'nested-child-selected': selectedChildId === child.tempId }">
                                        <i class="fas fa-grip-vertical text-muted me-2"></i>
                                        <span class="nested-child-label" x-text="getBlockTypeLabel(child.type)"></span>
                                        <span class="nested-child-summary" x-text="childSummary(child)"></span>
                                        <div class="nested-child-controls" @click.stop>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="moveChild(block.tempId, child.tempId, 'up')" title="Move up"><i class="fas fa-chevron-up"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" @click="moveChild(block.tempId, child.tempId, 'down')" title="Move down"><i class="fas fa-chevron-down"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" @click="deleteChild(block.tempId, child.tempId)" title="Delete"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="block.children.length === 0" class="nested-zone-empty small text-muted py-2">
                                    <i class="fas fa-arrow-down me-1"></i> Drag blocks here to nest them inside the columns container.
                                </div>
                                <template x-for="child in block.children" :key="'edit-' + child.tempId">
                                    <div x-show="selectedChildId === child.tempId" x-transition class="nested-child-edit" @click.stop>
                                        <template x-if="child.type === 'heading'">
                                            <div class="d-flex gap-2">
                                                <select class="form-select form-select-sm" style="width:auto" @focus="onFieldFocus()" x-model="child.content.level" @change="onFieldChange()">
                                                    <option value="h1">H1</option>
                                                    <option value="h2">H2</option>
                                                    <option value="h3">H3</option>
                                                    <option value="h4">H4</option>
                                                    <option value="h5">H5</option>
                                                    <option value="h6">H6</option>
                                                </select>
                                                <input type="text" class="form-control form-control-sm" @focus="onFieldFocus()" x-model="child.content.text" @change="onFieldChange()" placeholder="Heading text...">
                                            </div>
                                        </template>
                                        <template x-if="child.type === 'button'">
                                            <div class="d-grid gap-2">
                                                <input type="text" class="form-control form-control-sm" @focus="onFieldFocus()" x-model="child.content.text" @change="onFieldChange()" placeholder="Button text...">
                                                <input type="text" class="form-control form-control-sm" @focus="onFieldFocus()" x-model="child.content.url" @change="onFieldChange()" placeholder="Link URL...">
                                            </div>
                                        </template>
                                        <template x-if="child.type === 'image'">
                                            <input type="text" class="form-control form-control-sm" @focus="onFieldFocus()" x-model="child.content.image" @change="onFieldChange()" placeholder="Image URL or media path">
                                        </template>
                                        <template x-if="!['heading','button','image'].includes(child.type)">
                                            <textarea class="form-control form-control-sm" rows="3"
                                                      @focus="onFieldFocus()" x-model="child.content.text" @change="onFieldChange()"
                                                      placeholder="Content..."></textarea>
                                        </template>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" @click="selectChild(block.tempId, null)">Done</button>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <div class="card-footer bg-light py-2 small text-muted d-flex justify-content-between">
                            <span x-text="'Order: ' + block.order"></span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" @click="moveBlock(block.tempId, 'up')" :disabled="block.order === 1" title="Move up">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" @click="moveBlock(block.tempId, 'down')" :disabled="block.order === blocks.length" title="Move down">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" @click="deleteBlock(block.tempId)" title="Delete block">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-ref="dropIndicator" class="drop-indicator" style="display: none"></div>
            </div>

            <!-- Add Block modal -->
            <div class="modal fade" id="blockTypeModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Block</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <template x-for="[value, label] in Object.entries(blockTypes)" :key="value">
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn btn-outline-secondary w-100 text-start p-3"
                                                @click="addBlock(value, _addTargetParent)" data-bs-dismiss="modal">
                                            <i class="fas fa-plus-circle text-primary me-2"></i>
                                            <span x-text="label"></span>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <template x-if="patterns.length > 0">
                                <div class="mt-4">
                                    <div class="text-muted small text-uppercase fw-semibold mb-2">Patterns</div>
                                    <div class="row g-2">
                                        <template x-for="pattern in patterns" :key="pattern.id">
                                            <div class="col-6 col-md-4">
                                                <button type="button" class="btn btn-outline-secondary w-100 text-start p-3"
                                                        @click="insertPattern(pattern, _addTargetParent)" data-bs-dismiss="modal">
                                                    <i :class="'fas ' + pattern.icon + ' me-2 text-primary'"></i>
                                                    <span x-text="pattern.name"></span>
                                                    <div class="text-muted small fw-normal" x-text="pattern.blocks.length + ' block(s)'"></div>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <template x-if="reusableBlocks.length > 0">
                                <div class="mt-4">
                                    <div class="text-muted small text-uppercase fw-semibold mb-2">Reusable blocks</div>
                                    <div class="row g-2">
                                        <template x-for="rb in reusableBlocks" :key="rb.id">
                                            <div class="col-6 col-md-4">
                                                <button type="button" class="btn btn-outline-primary w-100 text-start p-3"
                                                        @click="insertReusableBlock(rb, _addTargetParent)" data-bs-dismiss="modal">
                                                    <i class="fas fa-clone me-2"></i>
                                                    <span x-text="rb.name"></span>
                                                    <div class="text-muted small fw-normal" x-text="rb.blocks.length + ' block(s)'"></div>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Media picker modal -->
            <div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Choose Media</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="search" class="form-control mb-3"
                                   placeholder="Search images by name or alt text"
                                   x-model="mediaSearch">
                            <div class="row g-3">
                                <template x-for="item in filteredMedia" :key="item.id">
                                    <div class="col-6 col-md-4">
                                        <button type="button" class="btn p-0 border rounded w-100" @click="selectMedia(item.path || item.url)">
                                            <img :src="item.url" class="img-fluid rounded-top" style="height:100px;width:100%;object-fit:cover" alt="">
                                            <span class="d-block small text-truncate px-2 py-1" x-text="item.name"></span>
                                        </button>
                                    </div>
                                </template>
                                <div class="col-12 text-muted small" x-show="!mediaLoading && filteredMedia.length === 0">
                                    No images found.
                                </div>
                                <div class="col-12 text-muted small" x-show="mediaLoading">
                                    Loading images…
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save as reusable block modal -->
            <div class="modal fade" id="saveReusableModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Save as Reusable Block</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small" x-show="selectedBlock()">
                                Saving <strong x-text="selectedBlock() ? getBlockTypeLabel(selectedBlock().type) : ''"></strong> as a reusable template.
                            </p>
                            <div class="mb-3">
                                <label class="form-label" for="reusableName">Name</label>
                                <input type="text" class="form-control" id="reusableName" x-model="reusableName" placeholder="e.g. Hero CTA">
                            </div>
                            <div class="mb-0">
                                <label class="form-label" for="reusableDescription">Description <span class="text-muted">(optional)</span></label>
                                <input type="text" class="form-control" id="reusableDescription" x-model="reusableDescription" placeholder="Short description">
                            </div>
                            <div class="alert alert-danger py-2 small mt-3" x-show="reusableError" x-text="reusableError"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" @click="saveReusable()" :disabled="reusableSaving || !reusableName.trim()">
                                <i class="fas fa-spinner fa-spin me-1" x-show="reusableSaving"></i>
                                <span x-text="reusableSaving ? 'Saving…' : 'Save Template'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live preview modal -->
            <div class="modal fade" id="blockPreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Live Preview</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="text-muted" x-show="previewLoading">Rendering preview…</div>
                            <div class="alert alert-danger" x-show="previewError" x-text="previewError"></div>
                            <div class="preview-content" x-show="!previewLoading && !previewError" x-html="previewHtml"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="openPreview()">
                                <i class="fas fa-rotate me-1"></i> Refresh
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .block-editor-container { position: relative; }
    .block-item { transition: box-shadow 0.2s ease, opacity 0.2s ease; }
    /* The global admin .card:hover rule translates the card on hover; the
       builder needs a stable list, so keep block items in place. */
    .block-editor-container .block-item:hover { transform: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .block-item .cursor-grab { cursor: grab; }
    .block-item:active .cursor-grab { cursor: grabbing; }
    .block-item.drag-source { opacity: 0.45; }
    .sortable-container { position: relative; }
    .drop-indicator {
        height: 4px;
        border-radius: 2px;
        background: var(--primary-color, #3b82f6);
        margin: 0.5rem 0;
        pointer-events: none;
    }
    .nested-zone {
        border: 1px dashed #dee2e6;
        border-radius: 0.5rem;
        padding: 0.75rem;
        margin-top: 0.75rem;
        background: #fafbfc;
        transition: border-color 0.15s, background 0.15s;
    }
    .nested-zone-active {
        border-color: var(--primary-color, #3b82f6);
        background: #eef4ff;
    }
    .nested-zone-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    .nested-child-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        margin-bottom: 0.375rem;
        background: #fff;
        cursor: pointer;
        font-size: 0.8rem;
    }
    .nested-child-item:hover { border-color: #c3cbd4; }
    .nested-child-selected { border-color: var(--primary-color, #3b82f6); box-shadow: 0 0 0 2px rgba(59,130,246,0.18); }
    .nested-child-label { font-weight: 600; min-width: 5.5rem; }
    .nested-child-summary { color: #6b7280; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
    .nested-child-controls { display: flex; gap: 0.25rem; }
    .nested-child-controls .btn { padding: 0.1rem 0.35rem; font-size: 0.65rem; }
    .nested-zone-empty { border: 1px dashed #e2e5ea; border-radius: 0.375rem; }
    .nested-child-edit { border-top: 1px dashed #e2e5ea; padding-top: 0.5rem; margin-top: 0.25rem; }
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
    .preview-content .block { margin-bottom: 0.75rem; }
    .preview-content iframe { max-width: 100%; }
</style>
@endpush

@push('scripts')
<script>
window.__blockEditorConfig = @json($editorConfig);

document.addEventListener('alpine:init', () => {
    Alpine.data('blockEditor', () => ({
        cfg: window.__blockEditorConfig || { blocks: [], blockTypes: {}, urls: {} },
        blocks: [],
        blockTypes: {},
        selectedBlockId: null,
        saving: false,
        savedMessage: '',
        saveError: '',
        past: [],
        future: [],
        deletedBlocks: [],
        tempCounter: 0,
        editSnapshot: null,
        mediaItems: [],
        mediaLoading: false,
        mediaSearch: '',
        mediaPickerTarget: null,
        previewHtml: '',
        previewLoading: false,
        previewError: '',
        reusableBlocks: [],
        patterns: [],
        reusableName: '',
        reusableDescription: '',
        reusableSaving: false,
        reusableError: '',
        selectedChildId: null,
        nestedZoneActive: null,
        _dragSource: null,
        _addTargetParent: null,

        get canUndo() { return this.past.length > 0; },
        get canRedo() { return this.future.length > 0; },

        get filteredMedia() {
            const q = this.mediaSearch.trim().toLowerCase();
            if (!q) return this.mediaItems;
            return this.mediaItems.filter(item =>
                (item.name || '').toLowerCase().includes(q) ||
                (item.alt || '').toLowerCase().includes(q));
        },

        normalizeBlockContent(type, content) {
            const c = JSON.parse(JSON.stringify(content ?? {}));
            if (type === 'gallery' && Array.isArray(c.images)) {
                c.imagesText = c.images.map(i => typeof i === 'string' ? i : (i?.url ?? '')).filter(Boolean).join('\n');
                delete c.images;
                c.layout = c.layout || 'grid';
                c.columns = c.columns ?? 3;
                c.gap = c.gap ?? 10;
            }
            return c;
        },

        init() {
            this.blockTypes = this.cfg.blockTypes || {};
            this.patterns = this.cfg.patterns || [];
            this.reusableBlocks = this.cfg.reusableBlocks || [];
            this.blocks = (this.cfg.blocks || []).map((block, index) => ({
                id: block.id ?? null,
                tempId: 'real-' + block.id,
                type: block.type,
                content: this.normalizeBlockContent(block.type, block.content ?? {}),
                settings: JSON.parse(JSON.stringify(block.settings ?? {})),
                order: block.order || index + 1,
                children: (block.children || []).map((child, cIndex) => ({
                    id: child.id ?? null,
                    tempId: 'real-' + child.id,
                    type: child.type,
                    content: this.normalizeBlockContent(child.type, child.content ?? {}),
                    settings: JSON.parse(JSON.stringify(child.settings ?? {})),
                    order: child.order || cIndex + 1
                }))
            }));
            this.tempCounter = 0;
        },

        // ----- helpers -----
        esc(value) {
            return String(value ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
        },

        getBlockTypeLabel(type) {
            return this.blockTypes[type] || type;
        },

        selectedBlock() {
            return this.blocks.find(b => b.tempId === this.selectedBlockId);
        },

        closeBlockMenu() {
            this.selectedBlockId = null;
        },

        selectBlock(tempId) {
            this.selectedBlockId = tempId;
        },

        toggleBlockMenu(tempId) {
            this.selectedBlockId = this.selectedBlockId === tempId ? null : tempId;
        },

        // ----- history (undo/redo) -----
        snapshotState() {
            return JSON.parse(JSON.stringify({ blocks: this.blocks, deletedBlocks: this.deletedBlocks }));
        },

        pushHistory() {
            this.past.push(this.snapshotState());
            if (this.past.length > 50) this.past.shift();
            this.future = [];
        },

        restoreState(snapshot) {
            this.blocks = snapshot.blocks.map(b => ({
                ...b,
                content: JSON.parse(JSON.stringify(b.content ?? {})),
                settings: JSON.parse(JSON.stringify(b.settings ?? {})),
                children: (b.children ?? []).map(c => ({
                    ...c,
                    content: JSON.parse(JSON.stringify(c.content ?? {})),
                    settings: JSON.parse(JSON.stringify(c.settings ?? {}))
                }))
            }));
            this.deletedBlocks = snapshot.deletedBlocks.map(b => ({ ...b }));
            this.selectedBlockId = null;
            this.selectedChildId = null;
        },

        undo() {
            if (!this.past.length) return;
            this.future.push(this.snapshotState());
            const snap = this.past.pop();
            this.restoreState(snap);
        },

        redo() {
            if (!this.future.length) return;
            this.past.push(this.snapshotState());
            const snap = this.future.pop();
            this.restoreState(snap);
        },

        onFieldFocus() {
            this.capturePreEdit();
        },

        capturePreEdit() {
            if (!this.editSnapshot) {
                this.editSnapshot = JSON.stringify(this.snapshotState());
            }
        },

        onFieldChange() {
            if (this.editSnapshot && this.editSnapshot !== JSON.stringify(this.snapshotState())) {
                this.past.push(JSON.parse(this.editSnapshot));
                if (this.past.length > 50) this.past.shift();
                this.future = [];
            }
            this.editSnapshot = null;
        },

        handleKeydown(event) {
            const inField = event.target && event.target.matches('input, textarea, select, [contenteditable="true"]');
            if (event.key === 'z' && (event.ctrlKey || event.metaKey) && !inField) {
                event.preventDefault();
                if (event.shiftKey) this.redo(); else this.undo();
            } else if (event.key === 'y' && (event.ctrlKey || event.metaKey) && !inField) {
                event.preventDefault();
                this.redo();
            } else if (this.selectedBlockId && !inField && !event.target.closest('button, a, .block-menu')) {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.navigateSelection(event.key === 'ArrowDown' ? 1 : -1);
                } else if (event.key === 'Delete' || event.key === 'Backspace') {
                    event.preventDefault();
                    this.deleteBlock(this.selectedBlockId);
                }
            }
        },

        navigateSelection(delta) {
            const ids = this.blocks.map(b => b.tempId);
            const index = ids.indexOf(this.selectedBlockId);
            const next = index + delta;
            if (next < 0 || next >= ids.length) return;
            this.selectedBlockId = ids[next];
            this.$nextTick(() => {
                const el = document.querySelector('[data-temp-id="' + CSS.escape(ids[next]) + '"]');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        },

        focusSelectedBlock() {
            const id = this.selectedBlockId;
            this.$nextTick(() => {
                const el = document.querySelector(
                    '[data-temp-id="' + CSS.escape(id) + '"] .block-edit-form input, ' +
                    '[data-temp-id="' + CSS.escape(id) + '"] .block-edit-form textarea, ' +
                    '[data-temp-id="' + CSS.escape(id) + '"] .block-edit-form select'
                );
                if (el) el.focus();
            });
        },

        // ----- block operations -----
        addBlock(type, parentTempId = null) {
            if (parentTempId && type === 'columns') {
                alert('Columns cannot be nested inside another container.');
                return;
            }
            this.pushHistory();
            const defaults = this.defaultContentFor(type);
            const newBlock = {
                id: null,
                tempId: 'tmp-' + (++this.tempCounter),
                type: type,
                content: defaults,
                settings: {},
                order: 0,
                children: []
            };

            if (parentTempId) {
                const container = this.blocks.find(b => b.tempId === parentTempId);
                if (!container) return;
                newBlock.order = (container.children?.length ?? 0) + 1;
                container.children = [...(container.children ?? []), newBlock];
                this.selectedChildId = newBlock.tempId;
                this.selectedBlockId = container.tempId;
            } else {
                newBlock.order = this.blocks.length + 1;
                this.blocks.push(newBlock);
                this.selectedBlockId = newBlock.tempId;
                this.selectedChildId = null;
            }

            this._addTargetParent = null;
            this.savedMessage = '';
            this.saveError = '';
        },

        // ----- nested child operations -----
        findContainerWithChild(childTempId) {
            return this.blocks.find(b => (b.children ?? []).some(c => c.tempId === childTempId));
        },

        selectChild(containerTempId, childTempId) {
            this.selectedChildId = this.selectedChildId === childTempId ? null : childTempId;
            if (childTempId) this.selectedBlockId = containerTempId;
        },

        childSummary(child) {
            const content = child.content ?? {};
            switch (child.type) {
                case 'heading': return (content.text ?? '').substring(0, 80);
                case 'button': return (content.text ?? '').substring(0, 80);
                case 'image': return (content.image ?? '').substring(0, 60);
                case 'gallery': return Array.isArray(content.imagesText) ? content.imagesText.length + ' images' : (content.imagesText ?? '').split('\n').filter(s => s.trim()).length + ' images';
                case 'divider': return '— divider —';
                case 'spacer': return (content.size ?? 40) + 'px spacer';
                case 'columns': return (child.children?.length ?? 0) + ' children';
                default: return (content.text ?? content.html ?? '').substring(0, 80);
            }
        },

        moveChild(containerTempId, childTempId, direction) {
            const container = this.blocks.find(b => b.tempId === containerTempId);
            if (!container || !container.children) return;
            const index = container.children.findIndex(c => c.tempId === childTempId);
            if (index === -1) return;
            const target = direction === 'up' ? index - 1 : index + 1;
            if (target < 0 || target >= container.children.length) return;

            this.pushHistory();
            [container.children[index], container.children[target]] = [container.children[target], container.children[index]];
            container.children.forEach((c, i) => { c.order = i + 1; });
        },

        deleteChild(containerTempId, childTempId) {
            const container = this.blocks.find(b => b.tempId === containerTempId);
            if (!container || !container.children) return;
            const index = container.children.findIndex(c => c.tempId === childTempId);
            if (index === -1) return;

            if (!confirm('Delete this nested block?')) return;

            this.pushHistory();
            const [child] = container.children.splice(index, 1);
            if (child.id) {
                // Persisted child — track for deletion on save.
                this.deletedBlocks.push({ ...child, parentTempId: containerTempId });
            }
            container.children.forEach((c, i) => { c.order = i + 1; });
            if (this.selectedChildId === childTempId) this.selectedChildId = null;
            this.savedMessage = '';
            this.saveError = '';
        },

        updateChildOrders(container) {
            (container.children ?? []).forEach((c, i) => { c.order = i + 1; });
        },

        defaultContentFor(type) {
            switch (type) {
                case 'text': return { text: '' };
                case 'heading': return { level: 'h2', text: 'New Heading' };
                case 'image': return { image: '', caption: '', showCaption: true };
                case 'callout': return { text: '', style: 'primary', icon: '' };
                case 'divider': return { style: 'solid', thickness: 1 };
                case 'quote': return { text: '', cite: '' };
                case 'button': return { text: 'Click me', url: '#', style: 'primary' };
                case 'spacer': return { size: 40 };
                case 'video': return { url: '', caption: '' };
                case 'html': return { html: '' };
                case 'gallery': return { imagesText: '', layout: 'grid', columns: 3, gap: 10 };
                case 'columns': return { columns: 2, gutter: 20 };
                default: return {};
            }
        },

        deleteBlock(tempId) {
            const block = this.blocks.find(b => b.tempId === tempId);
            if (!block) return;
            if (!confirm('Are you sure you want to delete this block?')) return;

            this.pushHistory();

            if (block.id) {
                // Persisted block — track for deletion on save.
                this.deletedBlocks.push({ ...block });
            }
            // Children that were dragged into this block (or already persisted
            // inside it) must be tracked too: the parent delete cascades for
            // persisted blocks, but children dragged into an unsaved parent
            // would otherwise linger in the database unseen.
            for (const child of (block.children ?? [])) {
                if (child.id) {
                    this.deletedBlocks.push({ ...child, parentTempId: block.tempId });
                }
            }

            this.blocks = this.blocks.filter(b => b.tempId !== tempId);
            this.updateBlockOrders();
            this.selectedBlockId = null;
            this.savedMessage = '';
            this.saveError = '';
        },

        moveBlock(tempId, direction) {
            const index = this.blocks.findIndex(b => b.tempId === tempId);
            if (index === -1) return;

            const target = direction === 'up' ? index - 1 : index + 1;
            if (target < 0 || target >= this.blocks.length) return;

            this.pushHistory();
            [this.blocks[index], this.blocks[target]] = [this.blocks[target], this.blocks[index]];
            this.updateBlockOrders();
        },

        updateBlockOrders() {
            this.blocks.forEach((block, index) => {
                block.order = index + 1;
            });
        },

        // ----- drag & drop -----
        // NOTE: nothing here assigns reactive state except on drop — assigning
        // state mid-drag would make Alpine re-render the dragged element and
        // break the drag. The pre-drag snapshot is captured at dragstart.
        onDragStart(event) {
            this._dragSnapshot = JSON.stringify(this.snapshotState());
            this._dragSource = { tempId: event.currentTarget.dataset.tempId || '', parentTempId: null };
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this._dragSource.tempId);
            event.currentTarget.classList.add('drag-source');
        },

        onChildDragStart(event, containerTempId) {
            this._dragSnapshot = JSON.stringify(this.snapshotState());
            this._dragSource = { tempId: event.currentTarget.dataset.tempId || '', parentTempId: containerTempId };
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', this._dragSource.tempId);
            event.currentTarget.classList.add('drag-source');
        },

        onDragOver(event) {
            event.preventDefault();
            const container = this.$refs.container;
            if (!container) return;

            const items = [...container.querySelectorAll('.block-item:not(.drag-source)')];
            let index = items.length;
            for (let i = 0; i < items.length; i++) {
                const rect = items[i].getBoundingClientRect();
                if (event.clientY < rect.top + rect.height / 2) {
                    index = i;
                    break;
                }
            }

            const indicator = this.$refs.dropIndicator;
            if (!indicator) return;
            if (index >= items.length) {
                container.appendChild(indicator);
            } else {
                items[index].before(indicator);
            }
            indicator.style.display = 'block';
        },

        onDrop(event) {
            event.preventDefault();
            this.hideIndicator();

            const source = this._dragSource;
            this.cleanupDragClasses();
            this.nestedZoneActive = null;
            if (!source || !source.tempId) return;

            // A nested child dropped onto the top-level list → move it to the
            // top level.
            if (source.parentTempId) {
                const container = this.blocks.find(b => b.tempId === source.parentTempId);
                if (!container || !container.children) return;
                const childIndex = container.children.findIndex(c => c.tempId === source.tempId);
                if (childIndex === -1) return;

                const [child] = container.children.splice(childIndex, 1);
                this.updateChildOrders(container);

                // Insert at the indicator position in the top-level list.
                const containerEl = this.$refs.container;
                const indicator = this.$refs.dropIndicator;
                const draggedEl = containerEl ? containerEl.querySelector('.block-item.drag-source') : null;
                const beforeCount = containerEl && indicator
                    ? [...containerEl.children].filter(el => el.classList.contains('block-item') && el !== draggedEl && el.compareDocumentPosition(indicator) & Node.DOCUMENT_POSITION_FOLLOWING).length
                    : this.blocks.length;

                child.children = [];
                child.order = 0;
                const clamped = Math.max(0, Math.min(beforeCount, this.blocks.length));
                this.blocks.splice(clamped, 0, child);
                this.updateBlockOrders();
                if (this.selectedChildId === source.tempId) this.selectedChildId = null;

                this.recordDragHistory();
                this.savedMessage = '';
                this.saveError = '';
                return;
            }

            const tempId = source.tempId;
            const draggedIndex = this.blocks.findIndex(b => b.tempId === tempId);
            if (draggedIndex === -1) return;

            const container = this.$refs.container;
            const indicator = this.$refs.dropIndicator;
            const draggedEl = container ? container.querySelector('.block-item.drag-source') : null;
            const beforeCount = container && indicator
                ? [...container.children].filter(el => el.classList.contains('block-item') && el !== draggedEl && el.compareDocumentPosition(indicator) & Node.DOCUMENT_POSITION_FOLLOWING).length
                : 0;

            const [dragged] = this.blocks.splice(draggedIndex, 1);
            const clamped = Math.max(0, Math.min(beforeCount, this.blocks.length));
            this.blocks.splice(clamped, 0, dragged);

            this.recordDragHistory();

            this.updateBlockOrders();
            this.savedMessage = '';
            this.saveError = '';
        },

        recordDragHistory() {
            if (this._dragSnapshot) {
                this.past.push(JSON.parse(this._dragSnapshot));
                if (this.past.length > 50) this.past.shift();
                this.future = [];
                this._dragSnapshot = null;
            }
        },

        // ----- nested container drop -----
        onNestedDragOver(event, containerTempId) {
            event.preventDefault();
            this.nestedZoneActive = containerTempId;
        },

        onNestedDragLeave() {
            this.nestedZoneActive = null;
        },

        onNestedDrop(event, containerTempId) {
            event.preventDefault();
            const source = this._dragSource;
            this.cleanupDragClasses();
            this.nestedZoneActive = null;
            if (!source || !source.tempId) return;

            const container = this.blocks.find(b => b.tempId === containerTempId);
            if (!container || container.type !== 'columns') return;

            // Compute insertion index by child rows above the drop point.
            const zone = event.currentTarget;
            const rows = [...zone.querySelectorAll('.nested-child-item')];
            let index = rows.length;
            for (let i = 0; i < rows.length; i++) {
                const rect = rows[i].getBoundingClientRect();
                if (event.clientY < rect.top + rect.height / 2) {
                    index = i;
                    break;
                }
            }

            // A container block with children cannot be nested inside another
            // container (single-level nesting).
            const draggedFromSource = source.parentTempId
                ? this.blocks.find(b => b.tempId === source.parentTempId)?.children?.find(c => c.tempId === source.tempId)
                : this.blocks.find(b => b.tempId === source.tempId);
            if (draggedFromSource && (draggedFromSource.children ?? []).length > 0) {
                alert('A container block with nested blocks cannot be nested inside another container.');
                this.cleanupDragClasses();
                this.nestedZoneActive = null;
                this._dragSource = null;
                this._dragSnapshot = null;
                return;
            }

            // Remove the dragged block from wherever it currently lives.
            let dragged;
            if (source.parentTempId) {
                const fromContainer = this.blocks.find(b => b.tempId === source.parentTempId);
                if (!fromContainer || !fromContainer.children) return;
                const fromIndex = fromContainer.children.findIndex(c => c.tempId === source.tempId);
                if (fromIndex === -1) return;
                [dragged] = fromContainer.children.splice(fromIndex, 1);
                this.updateChildOrders(fromContainer);
            } else {
                const topIndex = this.blocks.findIndex(b => b.tempId === source.tempId);
                if (topIndex === -1) return;
                [dragged] = this.blocks.splice(topIndex, 1);
                this.updateBlockOrders();
            }

            // Insert into the target container.
            dragged.children = [];
            dragged.order = index + 1;
            container.children = [...(container.children ?? [])];
            container.children.splice(Math.max(0, Math.min(index, container.children.length)), 0, dragged);
            this.updateChildOrders(container);

            this.recordDragHistory();

            if (this.selectedBlockId === source.tempId) this.selectedBlockId = null;
            if (this.selectedChildId === source.tempId) this.selectedChildId = null;
            this.selectedChildId = source.tempId;
            this.selectedBlockId = containerTempId;
            this.savedMessage = '';
            this.saveError = '';
        },

        onDragEnd() {
            this.hideIndicator();
            this.cleanupDragClasses();
            this._dragSnapshot = null;
            this._dragSource = null;
            this.nestedZoneActive = null;
        },

        cleanupDragClasses() {
            document.querySelectorAll('.block-item.drag-source, .nested-child-item.drag-source').forEach(el => el.classList.remove('drag-source'));
        },

        hideIndicator() {
            const indicator = this.$refs.dropIndicator;
            if (indicator) indicator.style.display = 'none';
        },

        // ----- preview rendering (XSS-safe) -----
        renderBlockPreview(block) {
            const content = block.content ?? {};
            const text = this.esc(content.text ?? '');

            switch (block.type) {
                case 'text':
                    return text
                        ? `<div class="text-muted"><i class="fas fa-paragraph me-1"></i>${this.esc(content.text).substring(0, 120)}${content.text.length > 120 ? '...' : ''}</div>`
                        : '<div class="text-muted"><i class="fas fa-paragraph me-1"></i>Empty text block</div>';
                case 'heading': {
                    const level = ['h1','h2','h3','h4','h5','h6'].includes(content.level) ? content.level : 'h2';
                    return `<${level} class="text-primary mb-1">${text || 'Heading Preview'}</${level}>`;
                }
                case 'image':
                    return content.image
                        ? `<img src="${this.esc(content.image)}" alt="Image" class="rounded" style="max-height:150px">`
                        : '<div class="text-muted"><i class="fas fa-image me-1"></i>No image selected</div>';
                case 'callout': {
                    const style = ['primary','secondary','success','danger','warning','info'].includes(content.style) ? content.style : 'primary';
                    return `<div class="callout-${style}">${this.esc(content.text).substring(0, 120) || 'Empty callout'}</div>`;
                }
                case 'divider': {
                    const style = ['solid','dashed','dotted'].includes(content.style) ? content.style : 'solid';
                    const thickness = Math.max(1, Math.min(10, parseInt(content.thickness, 10) || 1));
                    return `<div class="divider-${style}" style="border-top-width:${thickness}px"></div>`;
                }
                case 'quote':
                    return `<blockquote class="border-start border-4 border-primary ps-3 text-muted my-1">${this.esc(content.text).substring(0, 120) || 'Quote…'}${content.cite ? '<footer class="blockquote-footer">— ' + this.esc(content.cite) + '</footer>' : ''}</blockquote>`;
                case 'button':
                    return `<span class="btn btn-sm btn-${this.buttonStyle(content.style)}">${this.esc(content.text || 'Button')}</span>`;
                case 'spacer': {
                    const size = Math.max(10, Math.min(200, parseInt(content.size, 10) || 40));
                    return `<div class="text-muted small"><i class="fas fa-arrows-alt-v me-1"></i>Spacer — ${size}px</div>`;
                }
                case 'video':
                    return content.url
                        ? `<div class="text-muted"><i class="fas fa-video me-1"></i>Video: ${this.esc(content.url).substring(0, 60)}</div>`
                        : '<div class="text-muted"><i class="fas fa-video me-1"></i>No video URL</div>';
                case 'html':
                    return `<div class="text-muted small"><i class="fas fa-code me-1"></i>Custom HTML — ${this.esc((content.html ?? '').substring(0, 60))}</div>`;
                case 'gallery': {
                    const count = (content.imagesText ?? '').split('\n').filter(s => s.trim()).length;
                    const layout = content.layout || 'grid';
                    return `<div class="text-muted small"><i class="fas fa-images me-1"></i>Gallery — ${count} image${count === 1 ? '' : 's'} · ${layout}</div>`;
                }
                case 'columns': {
                    const count = Math.max(1, Math.min(6, parseInt(content.columns, 10) || 2));
                    const nestedCount = (block.children ?? []).length;
                    return `<div class="text-muted small"><i class="fas fa-columns me-1"></i>Columns — ${count}${nestedCount ? ` · ${nestedCount} nested` : ''}</div>`;
                }
                default:
                    return `<div class="text-muted"><i class="fas fa-cube me-1"></i>${this.esc(this.getBlockTypeLabel(block.type))}</div>`;
            }
        },

        buttonStyle(style) {
            return ['primary','secondary','success','danger'].includes(style) ? style : 'primary';
        },

        // ----- modals & media picker -----
        openAddModal(parentTempId = null) {
            this._addTargetParent = parentTempId;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('blockTypeModal')).show();
        },

        openMediaPicker(tempId, field) {
            this.mediaPickerTarget = { tempId, field };
            this.mediaSearch = '';
            this.mediaLoading = true;
            this.mediaItems = [];
            bootstrap.Modal.getOrCreateInstance(document.getElementById('mediaPickerModal')).show();

            fetch(this.cfg.urls.mediaPicker, { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(items => {
                    this.mediaItems = Array.isArray(items) ? items : [];
                    this.mediaLoading = false;
                })
                .catch(() => {
                    this.mediaItems = [];
                    this.mediaLoading = false;
                });
        },

        selectMedia(url) {
            const target = this.mediaPickerTarget;
            if (!target) return;
            const block = this.blocks.find(b => b.tempId === target.tempId);
            if (block) {
                block.content[target.field] = url;
            }
            this.mediaPickerTarget = null;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('mediaPickerModal')).hide();
        },

        // ----- reusable blocks -----
        openSaveReusableModal() {
            const block = this.selectedBlock();
            if (!block) return;
            this.reusableName = '';
            this.reusableDescription = '';
            this.reusableError = '';
            this.reusableSaving = false;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('saveReusableModal')).show();
        },

        async saveReusable() {
            const block = this.selectedBlock();
            if (!block || !this.reusableName.trim() || this.reusableSaving) return;
            this.reusableSaving = true;
            this.reusableError = '';

            const payload = {
                name: this.reusableName.trim(),
                description: this.reusableDescription.trim() || null,
                blocks: [{
                    type: block.type,
                    content: block.type === 'gallery'
                        ? { images: (block.content.imagesText ?? '').split('\n').map(s => s.trim()).filter(Boolean) }
                        : JSON.parse(JSON.stringify(block.content ?? {})),
                    settings: JSON.parse(JSON.stringify(block.settings ?? {})),
                }],
            };

            try {
                const res = await fetch(this.cfg.urls.reusableStore, {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Failed to save reusable block');
                }
                this.reusableBlocks.unshift({
                    id: data.reusableBlock.id,
                    name: data.reusableBlock.name,
                    description: data.reusableBlock.description,
                    blocks: data.reusableBlock.blocks,
                });
                bootstrap.Modal.getOrCreateInstance(document.getElementById('saveReusableModal')).hide();
                this.savedMessage = 'Reusable block saved';
                setTimeout(() => { this.savedMessage = ''; }, 3000);
            } catch (e) {
                this.reusableError = e.message || 'Save failed';
            } finally {
                this.reusableSaving = false;
            }
        },

        insertTemplate(templateBlocks, parentTempId = null) {
            this.pushHistory();

            // Insert into a container if a parent target was given.
            if (parentTempId) {
                if (templateBlocks.some(b => b.type === 'columns')) {
                    alert('Columns cannot be nested inside another container.');
                    return;
                }
                const container = this.blocks.find(b => b.tempId === parentTempId);
                if (!container) return;
                const maxOrder = (container.children ?? []).reduce((max, c) => Math.max(max, c.order || 0), 0);
                templateBlocks.forEach((templateBlock, index) => {
                    const content = JSON.parse(JSON.stringify(templateBlock.content ?? {}));
                    if (templateBlock.type === 'gallery' && Array.isArray(content.images)) {
                        content.imagesText = content.images.map(i => typeof i === 'string' ? i : (i?.url ?? '')).filter(Boolean).join('\n');
                        delete content.images;
                    }
                    container.children = [...(container.children ?? []), {
                        id: null,
                        tempId: 'temp-' + (++this.tempCounter),
                        type: templateBlock.type,
                        content,
                        settings: JSON.parse(JSON.stringify(templateBlock.settings ?? {})),
                        order: maxOrder + index + 1,
                        children: []
                    }];
                });
                this.updateChildOrders(container);
                this.selectedChildId = container.children[container.children.length - 1]?.tempId ?? null;
                this.selectedBlockId = container.tempId;
                this.savedMessage = '';
                this.saveError = '';
                this._addTargetParent = null;
                return;
            }

            const maxOrder = this.blocks.reduce((max, b) => Math.max(max, b.order || 0), 0);
            templateBlocks.forEach((templateBlock, index) => {
                const content = JSON.parse(JSON.stringify(templateBlock.content ?? {}));
                if (templateBlock.type === 'gallery' && Array.isArray(content.images)) {
                    content.imagesText = content.images.map(i => typeof i === 'string' ? i : (i?.url ?? '')).filter(Boolean).join('\n');
                    delete content.images;
                }
                this.blocks.push({
                    id: null,
                    tempId: 'temp-' + (++this.tempCounter),
                    type: templateBlock.type,
                    content,
                    settings: JSON.parse(JSON.stringify(templateBlock.settings ?? {})),
                    order: maxOrder + index + 1,
                    children: []
                });
            });
            this.updateBlockOrders();
            this.selectedBlockId = this.blocks[this.blocks.length - 1].tempId;
            this.selectedChildId = null;
            this.savedMessage = '';
            this.saveError = '';
            this._addTargetParent = null;
        },

        insertReusableBlock(rb, parentTempId = null) {
            this.insertTemplate(rb.blocks, parentTempId);
        },

        insertPattern(pattern, parentTempId = null) {
            this.insertTemplate(pattern.blocks, parentTempId);
        },

        // ----- live preview -----
        blocksPayload() {
            return this.blocks.map(b => ({
                id: b.id ?? null,
                type: b.type,
                content: b.type === 'gallery'
                    ? { images: (b.content.imagesText ?? '').split('\n').map(s => s.trim()).filter(Boolean) }
                    : { ...b.content },
                settings: b.settings ?? {},
                order: b.order,
                children: (b.children ?? []).map(c => ({
                    id: c.id ?? null,
                    type: c.type,
                    content: c.type === 'gallery'
                        ? { images: (c.content.imagesText ?? '').split('\n').map(s => s.trim()).filter(Boolean) }
                        : { ...c.content },
                    settings: c.settings ?? {},
                    order: c.order
                }))
            }));
        },

        async openPreviewOnSite() {
            // Push the live editor state so the frontend preview renders it,
            // then open the frontend preview in a new tab.
            try {
                await fetch(this.cfg.urls.preview, {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({ blocks: this.blocksPayload() }),
                });
            } catch (e) {
                // Open the preview anyway — it renders persisted state.
            }
            if (this.cfg.urls.previewPage) {
                window.open(this.cfg.urls.previewPage, '_blank');
            }
        },

        async openPreview() {
            this.previewLoading = true;
            this.previewError = '';
            this.previewHtml = '';
            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('blockPreviewModal'));
            modal.show();

            try {
                const res = await fetch(this.cfg.urls.preview, {
                    method: 'POST',
                    headers: this.jsonHeaders(),
                    body: JSON.stringify({ blocks: this.blocksPayload() })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    throw new Error(data.error || 'Preview failed');
                }
                this.previewHtml = data.html;
            } catch (e) {
                this.previewError = e.message || 'Preview failed';
            } finally {
                this.previewLoading = false;
            }
        },

        // ----- save -----
        jsonHeaders() {
            return {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            };
        },

        async save() {
            if (this.saving) return;
            this.saving = true;
            this.saveError = '';
            this.savedMessage = '';

            try {
                const createdByTempId = {};
                const createdChildByTempId = {};

                // 1. Create new (unsaved) blocks.
                for (const block of this.blocks.filter(b => !b.id)) {
                    const payload = this.savePayloadFor(block);
                    const res = await fetch(this.cfg.urls.store, {
                        method: 'POST',
                        headers: this.jsonHeaders(),
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Failed to create block');
                    }
                    createdByTempId[block.tempId] = data.block.id;
                }

                // 2. Create unsaved children (after their parents exist).
                for (const block of this.blocks) {
                    const parentRealId = block.id ?? createdByTempId[block.tempId];
                    if (!parentRealId) continue;
                    for (const child of (block.children ?? [])) {
                        if (child.id) continue;
                        const payload = this.savePayloadFor(child);
                        const res = await fetch(this.cfg.urls.store, {
                            method: 'POST',
                            headers: this.jsonHeaders(),
                            body: JSON.stringify({ ...payload, parent_id: parentRealId })
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            throw new Error(data.message || 'Failed to create child block');
                        }
                        createdChildByTempId[child.tempId] = data.block.id;
                    }
                }

                // 3. Update existing top-level blocks.
                for (const block of this.blocks.filter(b => b.id)) {
                    const res = await fetch(this.cfg.urls.update.replace('__ID__', block.id), {
                        method: 'PUT',
                        headers: this.jsonHeaders(),
                        body: JSON.stringify(this.savePayloadFor(block))
                    });
                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        throw new Error(data.message || 'Failed to update block');
                    }
                }

                // 4. Update existing children (including any parent change).
                for (const block of this.blocks) {
                    const parentRealId = block.id ?? createdByTempId[block.tempId];
                    if (!parentRealId) continue;
                    for (const child of (block.children ?? [])) {
                        if (!child.id) continue;
                        const res = await fetch(this.cfg.urls.update.replace('__ID__', child.id), {
                            method: 'PUT',
                            headers: this.jsonHeaders(),
                            body: JSON.stringify({ ...this.savePayloadFor(child), parent_id: parentRealId })
                        });
                        const data = await res.json();
                        if (!res.ok || !data.success) {
                            throw new Error(data.message || 'Failed to update child block');
                        }
                    }
                }

                // 5. Delete blocks removed since load. A 404 means the block
                // was already removed (e.g. cascaded with a parent delete) —
                // treat it as success.
                for (const block of this.deletedBlocks) {
                    // Unsaved blocks never existed in the DB.
                    if (!block.id) continue;
                    const res = await fetch(this.cfg.urls.destroy.replace('__ID__', block.id), {
                        method: 'DELETE',
                        headers: this.jsonHeaders()
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.status === 404) continue;
                    if (!res.ok || (data.success === false)) {
                        throw new Error('Failed to delete a removed block');
                    }
                }

                // 6. Reorder top-level blocks (all real ids, in final order).
                const finalIds = this.blocks.map(b => b.id ?? createdByTempId[b.tempId]).filter(Boolean);
                if (finalIds.length > 0) {
                    const res = await fetch(this.cfg.urls.reorder, {
                        method: 'POST',
                        headers: this.jsonHeaders(),
                        body: JSON.stringify({ order: finalIds })
                    });
                    const reorderData = await res.json().catch(() => ({}));
                    if (!res.ok || reorderData.success === false) {
                        throw new Error('Failed to save block order');
                    }
                }

                // 7. Reorder children of each container.
                for (const block of this.blocks) {
                    if (!block.children || block.children.length === 0) continue;
                    const childIds = block.children.map(c => c.id ?? createdChildByTempId[c.tempId]).filter(Boolean);
                    if (childIds.length === 0) continue;
                    const res = await fetch(this.cfg.urls.reorder, {
                        method: 'POST',
                        headers: this.jsonHeaders(),
                        body: JSON.stringify({ order: childIds })
                    });
                    const childReorderData = await res.json().catch(() => ({}));
                    if (!res.ok || childReorderData.success === false) {
                        throw new Error('Failed to save child block order');
                    }
                }

                // 8. Update local state with the real ids and clear history.
                this.blocks = this.blocks.map(b => ({
                    ...b,
                    id: b.id ?? createdByTempId[b.tempId] ?? null,
                    children: (b.children ?? []).map(c => ({
                        ...c,
                        id: c.id ?? createdChildByTempId[c.tempId] ?? null
                    }))
                }));
                this.deletedBlocks = [];
                this.past = [];
                this.future = [];
                this.editSnapshot = null;
                this.savedMessage = 'Saved';
                setTimeout(() => { this.savedMessage = ''; }, 3000);
            } catch (e) {
                this.saveError = e.message || 'Save failed';
            } finally {
                this.saving = false;
            }
        },

        savePayloadFor(block) {
            const content = { ...block.content };
            if (block.type === 'gallery' && content.imagesText !== undefined) {
                content.images = content.imagesText.split('\n').map(s => s.trim()).filter(Boolean);
                delete content.imagesText;
            }
            if (block.type === 'gallery') {
                content.layout = content.layout || 'grid';
                content.columns = parseInt(content.columns, 10) || 3;
                content.gap = parseInt(content.gap, 10) || 10;
            }
            return {
                type: block.type,
                content: content,
                settings: block.settings ?? {},
                order: block.order
            };
        }
    }));
});
</script>
@endpush
