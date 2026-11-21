@extends('admin.layouts.app')

@section('content')
    @php
        $typeOptions = ['page' => 'Page', 'post' => 'Post', 'custom' => 'Custom URL'];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Edit menu: {{ $menu->name }}</h1>
            <p class="text-muted mb-0">Add pages, posts, or custom links and arrange them in the navigation.</p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary">Back to menus</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- Menu items with drag-and-drop --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Menu structure (drag to reorder)</h2>
                    <span class="badge bg-light text-dark">{{ $allItems->count() }} item(s)</span>
                </div>
                <div class="card-body">
                    <div x-data="menuEditor()" x-init="init()" class="menu-editor-container">
                        @if($rootItems->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-list fa-3x mb-3 opacity-50"></i>
                                <p class="mb-0">No menu items yet. Add your first item using the form on the right.</p>
                            </div>
                        @else
                            <ul class="list-group list-group-flush" x-ref="menuList">
                                @foreach($rootItems as $item)
                                    @include('admin.menus.partials.menu-item', ['item' => $item])
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <button type="button" class="btn btn-sm btn-success" @click="saveMenuOrder()">
                        <i class="fas fa-save me-1"></i> Save Menu Order
                    </button>
                </div>
            </div>
        </div>

        {{-- Sidebar: menu settings + add item --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h2 class="h5 mb-0">Menu settings</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.menus.update', $menu) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="menu-name" class="form-label">Name</label>
                            <input type="text" name="name" id="menu-name" class="form-control"
                                   value="{{ old('name', $menu->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="menu-location" class="form-label">Location</label>
                            <select name="location" id="menu-location" class="form-select">
                                @foreach(['header' => 'Header', 'footer' => 'Footer', 'custom' => 'Custom'] as $value => $label)
                                    <option value="{{ $value }}" {{ old('location', $menu->location) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save menu settings</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h2 class="h5 mb-0">Add menu item</h2>
                </div>
                <div class="card-body">
                    @include('admin.menus.partials.item-form', [
                        'item' => null,
                        'action' => route('admin.menus.items.store', $menu),
                        'method' => 'POST',
                        'parents' => $allItems,
                        'content' => $content,
                        'typeOptions' => $typeOptions,
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .menu-editor-container { position: relative; }
    .menu-item-drag { cursor: grab; }
    .menu-item-drag:active { cursor: grabbing; }
    .menu-item-drag .d-flex { transition: all 0.2s ease; }
    .menu-item-drag:hover .d-flex { background-color: #f8f9fa; }
    .menu-placeholder {
        border: 2px dashed #dee2e6;
        border-radius: 0.5rem;
        min-height: 60px;
        background: #f8f9fa;
        margin: 0.25rem 0;
    }
    .menu-item-children { margin-left: 1.5rem; border-left: 2px solid #dee2e6; padding-left: 1rem; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('menuEditor', () => ({
        items: @json($allItems->keyBy('id')),
        
        init() {
            this.setupDragAndDrop();
        },
        
        setupDragAndDrop() {
            const container = this.$refs.menuList;
            if (!container) return;
            
            let draggedItem = null;
            let placeholder = null;
            
            container.addEventListener('dragstart', (e) => {
                draggedItem = e.target.closest('[data-menu-item-id]');
                if (!draggedItem) return;
                
                e.target.classList.add('opacity-50');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', draggedItem.dataset.menuItemId);
                
                // Create placeholder
                placeholder = document.createElement('li');
                placeholder.className = 'menu-placeholder list-group-item';
                placeholder.innerText = 'Drop here';
                draggedItem.parentNode.insertBefore(placeholder, draggedItem);
            });
            
            container.addEventListener('dragend', (e) => {
                e.target.classList.remove('opacity-50');
                if (placeholder && placeholder.parentNode) {
                    placeholder.parentNode.removeChild(placeholder);
                }
                draggedItem = null;
                placeholder = null;
            });
            
            container.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const afterElement = this.getDragAfterElement(container, e.clientY);
                if (placeholder && afterElement != null) {
                    container.insertBefore(placeholder, afterElement);
                }
            });
            
            container.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const itemId = e.dataTransfer.getData('text/plain');
                const draggedEl = container.querySelector(`[data-menu-item-id="${itemId}"]`);
                
                if (draggedEl && placeholder) {
                    if (afterElement) {
                        container.insertBefore(draggedEl, placeholder);
                    } else {
                        container.appendChild(draggedEl);
                    }
                }
            });
        },
        
        getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('[data-menu-item-id]')];
            
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
        
        saveMenuOrder() {
            const order = [];
            
            function collectOrder(items) {
                items.forEach((item, index) => {
                    const id = item.dataset.menuItemId;
                    order.push({
                        id: id,
                        parent_id: item.dataset.parentId || null,
                        order: index + 1
                    });
                    
                    const childrenContainer = item.querySelector('.menu-item-children ul');
                    if (childrenContainer) {
                        collectOrder([...childrenContainer.children]);
                    }
                });
            }
            
            collectOrder([...this.$refs.menuList.children]);
            
            fetch('{{ route("admin.menus.reorder", $menu->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="csrf-token"]')?.content ||
                                  document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ items: order })
            })
            .then(response => response.json())
            .then(() => {
                alert('Menu order saved successfully!');
            })
            .catch(err => console.error('Save error:', err));
        }
    }));
});
</script>
@endpush
