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

    @php
        $translationLanguages = \App\Models\Language::where('is_active', true)->where('is_default', false)->orderBy('sort_order')->get();
        $existingTranslations = $item
            ? \App\Models\LanguageContent::where('content_type', 'menu_item')->where('content_id', $item->id)->get()->keyBy('language_id')
            : collect();
    @endphp
    @if($translationLanguages->isNotEmpty())
        <div class="mb-3">
            <label class="form-label">Translated labels</label>
            @foreach($translationLanguages as $lang)
                @php $translation = $existingTranslations->get($lang->id); @endphp
                <div class="mb-2">
                    <label class="form-label small text-muted" for="menu-translation-title-{{ $lang->id }}">{{ $lang->flag }} {{ $lang->name }}</label>
                    <input type="text" name="translations[{{ $lang->id }}][title]" id="menu-translation-title-{{ $lang->id }}" class="form-control form-control-sm"
                           maxlength="255"
                           value="{{ old('translations.' . $lang->id . '.title', $translation->title ?? '') }}">
                </div>
            @endforeach
        </div>
    @endif

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

<script>
(function () {
    var form = document.querySelector('[data-menu-item-form]');
    if (!form) return;

    var typeSelect = form.querySelector('[data-menu-type]');
    var contentPicker = form.querySelector('[data-content-picker]');
    var urlPicker = form.querySelector('[data-url-picker]');
    if (!typeSelect || !contentPicker || !urlPicker) return;

    var urlInput = urlPicker.querySelector('input[name="url"]');

    function sync() {
        var isCustom = typeSelect.value === 'custom';
        contentPicker.classList.toggle('d-none', isCustom);
        urlPicker.classList.toggle('d-none', !isCustom);
        if (urlInput) {
            urlInput.required = isCustom;
        }
    }

    typeSelect.addEventListener('change', sync);
    sync();
})();
</script>
