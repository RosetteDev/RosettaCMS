<li class="list-group-item menu-item-drag" data-menu-item-id="{{ $item->id }}" data-parent-id="{{ $item->parent_id ?? 'null' }}">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="fas fa-grip-vertical text-muted me-2 cursor-grab"></i>
            <div>
                <strong class="text-dark">{{ $item->title }}</strong>
                <div class="small text-muted">
                    @if($item->type === 'page' && $item->page)
                        Page: {{ $item->page->title }}
                    @elseif($item->type === 'post' && $item->post)
                        Post: {{ $item->post->title }}
                    @else
                        Custom URL: {{ $item->url ?? 'N/A' }}
                    @endif
                </div>
            </div>
        </div>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="dropdown">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" href="#" onclick="editMenuItem({{ $item->id }}); return false;">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#" onclick="addChildItem({{ $item->id }}); return false;">
                        <i class="fas fa-plus-circle me-1"></i> Add Child
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="#" onclick="deleteMenuItem({{ $item->id }}); return false;">
                        <i class="fas fa-trash me-1"></i> Delete
                    </a>
                </li>
            </ul>
        </div>
    </div>
    @if($item->children->isNotEmpty())
        <div class="menu-item-children mt-2">
            <ul class="list-group list-group-flush">
                @foreach($item->children as $child)
                    @include('admin.menus.partials.menu-item', ['item' => $child])
                @endforeach
            </ul>
        </div>
    @endif
</li>
