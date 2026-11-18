@php
    $isEdit = $item !== null;
    $currentType = old('type', $item->type ?? 'page');
    $currentParent = old('parent_id', $item->parent_id ?? '');
@endphp

<form method="POST" action="{{ $action }}" data-menu-item-form>
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="mb-3">
        <label class="form-label">Label</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $item->title ?? '') }}"
               placeholder="Home" required maxlength="255">
        <div class="form-text">The text visitors see in the menu.</div>
    </div>

    <div class="mb-3">
        <label class="form-label">Link type</label>
        <select name="type" class="form-select" data-menu-type>
            @foreach($typeOptions as $value => $label)
                <option value="{{ $value }}" {{ $currentType === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3" data-content-picker>
        <label class="form-label">Content</label>
        <select name="page_id" class="form-select">
            <option value="">— Select —</option>
            @foreach($content as $entry)
                <option value="{{ $entry->id }}"
                        data-entry-type="{{ $entry->post_type }}"
                        {{ (string) old('page_id', $item->page_id ?? '') === (string) $entry->id ? 'selected' : '' }}>
                    {{ $entry->title }} ({{ $entry->post_type }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Choose the page or post this item links to.</div>
    </div>

    <div class="mb-3 d-none" data-url-picker>
        <label class="form-label">URL</label>
        <input type="text" name="url" class="form-control" value="{{ old('url', $item->url ?? '') }}"
               placeholder="https://example.com or /relative-path">
    </div>

    <div class="row g-2">
        <div class="col-sm-7">
            <label class="form-label">Parent</label>
            <select name="parent_id" class="form-select">
                <option value="">— Top level —</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ (string) $currentParent === (string) $parent->id ? 'selected' : '' }}>
                        {{ $parent->title }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-5">
            <label class="form-label">Order</label>
            <input type="number" name="order" class="form-control" min="0"
                   value="{{ old('order', $item->order ?? 0) }}">
        </div>
    </div>

    <div class="mb-3 mt-2">
        <label class="form-label">Open in</label>
        <select name="target" class="form-select">
            <option value="_self" {{ old('target', $item->target ?? '_self') === '_self' ? 'selected' : '' }}>Same window</option>
            <option value="_blank" {{ old('target', $item->target ?? '_self') === '_blank' ? 'selected' : '' }}>New window</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary w-100">
        {{ $isEdit ? 'Update item' : 'Add item' }}
    </button>
</form>
