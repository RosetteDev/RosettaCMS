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
        {{-- Menu items --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Menu structure</h2>
                    <span class="badge bg-light text-dark">{{ $allItems->count() }} item(s)</span>
                </div>
                <div class="card-body">
                    @if($items->isEmpty())
                        <p class="text-muted mb-0">No items yet. Add your first item using the form on the right.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($items as $item)
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="fw-semibold">{{ $item->title }}</span>
                                            <span class="badge bg-light text-dark ms-2 text-capitalize">{{ $item->type }}</span>
                                            <div class="small text-muted text-truncate" style="max-width: 420px;">
                                                {{ $item->resolved_url }}
                                            </div>
                                        </div>
                                        <div class="text-nowrap ms-3">
                                            <button class="btn btn-sm btn-outline-primary" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#edit-item-{{ $item->id }}">
                                                Edit
                                            </button>
                                            <form action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Remove this menu item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                            </form>
                                        </div>
                                    </div>

                                    @if($item->children->isNotEmpty())
                                        <ul class="list-group list-group-flush mt-2 ms-4 border-start ps-3">
                                            @foreach($item->children as $child)
                                                <li class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span>{{ $child->title }}</span>
                                                        <span class="badge bg-light text-dark ms-2 text-capitalize">{{ $child->type }}</span>
                                                        <div class="small text-muted text-truncate" style="max-width: 360px;">{{ $child->resolved_url }}</div>
                                                    </div>
                                                    <div class="text-nowrap ms-3">
                                                        <button class="btn btn-sm btn-outline-primary" type="button"
                                                                data-bs-toggle="collapse" data-bs-target="#edit-item-{{ $child->id }}">Edit</button>
                                                        <form action="{{ route('admin.menus.items.destroy', [$menu, $child]) }}" method="POST" class="d-inline"
                                                              onsubmit="return confirm('Remove this menu item?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                                        </form>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    <div class="collapse mt-3" id="edit-item-{{ $item->id }}">
                                        @include('admin.menus.partials.item-form', [
                                            'item' => $item,
                                            'action' => route('admin.menus.items.update', [$menu, $item]),
                                            'method' => 'PUT',
                                            'parents' => $allItems->where('id', '!=', $item->id),
                                            'content' => $content,
                                            'typeOptions' => $typeOptions,
                                        ])
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar: menu settings + add item --}}
        <div class="col-lg-5">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-menu-item-form]').forEach(function (form) {
        const typeSelect = form.querySelector('select[name="type"]');
        const contentWrap = form.querySelector('[data-content-picker]');
        const urlWrap = form.querySelector('[data-url-picker]');

        const contentSelect = form.querySelector('select[name="page_id"]');

        function sync() {
            const type = typeSelect.value;
            const isCustom = type === 'custom';

            if (contentWrap) contentWrap.classList.toggle('d-none', isCustom);
            if (urlWrap) urlWrap.classList.toggle('d-none', !isCustom);

            if (contentSelect) {
                contentSelect.querySelectorAll('option[data-entry-type]').forEach(function (option) {
                    const matches = isCustom || option.dataset.entryType === type;
                    option.hidden = !matches;
                    option.disabled = !matches;
                });

                const selected = contentSelect.options[contentSelect.selectedIndex];
                if (selected && selected.disabled) {
                    contentSelect.value = '';
                }
            }
        }

        typeSelect.addEventListener('change', sync);
        sync();
    });
});
</script>
@endpush
