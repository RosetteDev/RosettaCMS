@extends('admin.layouts.app')

@section('title', 'Settings — ' . $sections[$section])

@push('styles')
<style>
    .settings-tabs {
        display: flex;
        gap: 0.25rem;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-bottom: 1px solid var(--border-color);
        padding: 0.5rem 1.5rem 0;
        position: sticky;
        top: 0;
        z-index: 40;
        background-color: var(--bg-color, #f9fafb);
    }
    .settings-tabs a {
        display: inline-block;
        padding: 0.65rem 1rem;
        border-radius: 6px 6px 0 0;
        color: var(--text-color);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        white-space: nowrap;
        border: 1px solid transparent;
        border-bottom: none;
        background-color: transparent;
    }
    .settings-tabs a:hover:not(.active) { background-color: var(--light-color); }
    .settings-tabs a.active {
        background-color: var(--light-color);
        border-color: var(--border-color);
        color: var(--primary-color);
        font-weight: 600;
    }
    .settings-card { background-color: var(--light-color); border-radius: 8px; padding: 2rem; }
    @media (max-width: 991px) {
        .settings-card { padding: 1.25rem; }
        .settings-container { padding: 1rem !important; }
    }
</style>
@endpush

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Settings</h1>
            <p>{{ $sections[$section] }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="container">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif

    <div class="settings-tabs" role="tablist" aria-label="Settings sections">
        @foreach($sections as $key => $title)
            <a href="{{ route('admin.settings.edit', ['section' => $key]) }}" role="tab" aria-selected="{{ $key === $section ? 'true' : 'false' }}" class="{{ $key === $section ? 'active' : '' }}">{{ $title }}</a>
        @endforeach
    </div>

    <div class="container settings-container" style="max-width: 1200px; padding: 1.5rem 1.5rem 2rem;">
        <form action="{{ route('admin.settings.update', ['section' => $section]) }}" method="POST">
            @csrf

            <div class="settings-card">
                @include('admin.settings.sections.' . $section)
            </div>

            <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                <a href="{{ route('dashboard') }}" style="color: var(--text-color); text-decoration: none;">Cancel</a>
                <button type="submit" style="background-color: var(--primary-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-size: 1rem; font-weight: 500; cursor: pointer;">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    @if($section === 'seo')
        @include('admin.settings._og-modal')
    @endif
@endsection

@push('scripts')
@if($section === 'seo')
<script>
    // OG image picker
    const ogPickerModal = document.getElementById('ogImagePickerModal');
    const ogPickerGrid = document.getElementById('og-image-picker-grid');
    const ogPickerSearch = document.getElementById('og-image-picker-search');
    const ogImageInput = document.getElementById('og_image');
    let ogPickerLoaded = false;
    let ogPickerTimer = null;

    const renderOgPicker = (items) => {
        if (!ogPickerGrid) return;

        if (!items.length) {
            ogPickerGrid.innerHTML = '<div class="col-12 text-muted small">No images found. <a href="{{ url('/admin/media') }}">Upload one</a>.</div>';
            return;
        }

        ogPickerGrid.innerHTML = items.map((item) => `
            <div class="col-6 col-md-4">
                <button type="button" class="btn p-0 border rounded w-100 og-image-pick"
                        data-url="${item.url.replace(/"/g, '&quot;')}"
                        data-path="${item.path.replace(/"/g, '&quot;')}"
                        data-alt="${(item.alt || '').replace(/"/g, '&quot;')}">
                    <img src="${item.url}" alt="${(item.alt || item.name).replace(/"/g, '&quot;')}"
                         class="img-fluid rounded-top" style="height:110px;width:100%;object-fit:cover;">
                    <span class="d-block small text-truncate px-2 py-1">${item.name}</span>
                </button>
            </div>
        `).join('');
    };

    const loadOgPicker = (search = '') => {
        if (!ogPickerGrid) return;

        ogPickerGrid.innerHTML = '<div class="col-12 text-muted small">Loading images…</div>';

        fetch(`{{ route('admin.media.picker') }}?search=${encodeURIComponent(search)}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then((response) => response.ok ? response.json() : [])
            .then(renderOgPicker)
            .catch(() => renderOgPicker([]));
    };

    if (ogPickerModal) {
        ogPickerModal.addEventListener('show.bs.modal', () => {
            if (!ogPickerLoaded) {
                ogPickerLoaded = true;
                loadOgPicker();
            }
        });
    }

    if (ogPickerSearch) {
        ogPickerSearch.addEventListener('input', () => {
            clearTimeout(ogPickerTimer);
            ogPickerTimer = setTimeout(() => loadOgPicker(ogPickerSearch.value.trim()), 250);
        });
    }

    if (ogPickerGrid) {
        ogPickerGrid.addEventListener('click', (event) => {
            const button = event.target.closest('.og-image-pick');
            if (!button) return;

            if (ogImageInput) {
                // Store the full URL — OG tags must be absolute.
                ogImageInput.value = button.dataset.url || button.dataset.path || '';
            }

            if (window.bootstrap && ogPickerModal) {
                bootstrap.Modal.getOrCreateInstance(ogPickerModal).hide();
            }
        });
    }

    // Custom form fields editor
    const cffList = document.getElementById('custom-form-fields-list');
    const cffJson = document.getElementById('custom_form_fields_json');
    const cffForm = document.querySelector('form[action="{{ route('admin.settings.update', ['section' => $section]) }}"]');

    const cffRowTemplate = (name = '', label = '', type = 'text', placeholder = '', required = false) => `
        <div class="cff-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; align-items: end; background-color: rgba(0,0,0,0.02); border: 1px solid var(--border-color); border-radius: 6px; padding: 0.75rem;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Name</label>
                <input type="text" class="cff-name form-control form-control-sm" value="${name.replace(/"/g, '&quot;')}" placeholder="field_name" required>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Label</label>
                <input type="text" class="cff-label form-control form-control-sm" value="${label.replace(/"/g, '&quot;')}" placeholder="Field label">
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Type</label>
                <select class="cff-type form-select form-select-sm">
                    <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
                    <option value="email" ${type === 'email' ? 'selected' : ''}>Email</option>
                    <option value="textarea" ${type === 'textarea' ? 'selected' : ''}>Textarea</option>
                    <option value="number" ${type === 'number' ? 'selected' : ''}>Number</option>
                </select>
            </div>
            <button type="button" class="cff-remove btn btn-danger btn-sm" title="Remove field" style="padding: 0.4rem 0.7rem;">✕</button>
            <div style="grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 0.5rem; align-items: end;">
                <div>
                    <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Placeholder (optional)</label>
                    <input type="text" class="cff-placeholder form-control form-control-sm" value="${placeholder.replace(/"/g, '&quot;')}" placeholder="Placeholder text">
                </div>
                <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem; color: var(--text-color); padding-bottom: 0.35rem;">
                    <input type="checkbox" class="cff-required" value="1" ${required ? 'checked' : ''}> Required
                </label>
            </div>
        </div>`;

    const serializeCff = () => {
        const fields = [];
        document.querySelectorAll('#custom-form-fields-list .cff-row').forEach((row) => {
            const name = row.querySelector('.cff-name').value.trim();
            if (!name) return;
            fields.push({
                name: name,
                label: row.querySelector('.cff-label').value.trim() || name,
                type: row.querySelector('.cff-type').value,
                placeholder: row.querySelector('.cff-placeholder').value.trim(),
                required: row.querySelector('.cff-required').checked,
            });
        });
        if (cffJson) cffJson.value = JSON.stringify(fields);
    };

    if (cffList) {
        cffList.addEventListener('click', (event) => {
            const removeBtn = event.target.closest('.cff-remove');
            if (removeBtn) {
                removeBtn.closest('.cff-row').remove();
            }
        });
    }

    const cffAdd = document.getElementById('cff-add');
    if (cffAdd && cffList) {
        cffAdd.addEventListener('click', () => {
            cffList.insertAdjacentHTML('beforeend', cffRowTemplate());
            const newRow = cffList.lastElementChild;
            const input = newRow.querySelector('.cff-name');
            if (input) input.focus();
        });
    }

    if (cffForm) {
        cffForm.addEventListener('submit', serializeCff);
    }
</script>
@endif
@endpush
