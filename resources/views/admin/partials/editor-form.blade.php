@php
    $isEdit = $post->exists;
    $base = $postType === 'page' ? '/admin/pages' : '/admin/posts';
    $formAction = $isEdit ? url($base . '/' . $post->id) : url($base);
    $indexUrl = url($base);
    $meta = $post->meta ?? [];
    $label = $postType === 'page' ? 'Page' : 'Post';
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $action }} {{ $label }}</h1>
        <p class="text-muted mb-0">{{ $isEdit ? 'Update your content and settings' : 'Create new ' . strtolower($label) . ' content' }}</p>
    </div>
    <div class="mt-3 mt-sm-0">
        <a href="{{ $indexUrl }}" class="btn btn-outline-secondary">Back to {{ $label }}s</a>
    </div>
</div>

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

<form id="post-form" method="POST" action="{{ $formAction }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row g-4">
        {{-- Main column --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Title</label>
                        <input type="text" name="title" id="title" class="form-control form-control-lg"
                               value="{{ old('title', $post->title) }}" required maxlength="255"
                               placeholder="Add a title...">
                    </div>

                    <div class="mb-3">
                        <label for="slug" class="form-label fw-semibold">Slug</label>
                        <div class="input-group">
                            <span class="input-group-text">{{ url('/') }}/</span>
                            <input type="text" name="slug" id="slug" class="form-control"
                                   value="{{ old('slug', $post->slug) }}" placeholder="auto-generated-from-title">
                        </div>
                        <div class="form-text">Leave blank to generate from the title.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Content</label>
                        <div id="quill-editor"></div>
                        <textarea name="content" id="content" style="display: none;">{{ old('content', $post->content) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h2 class="h5 mb-0">Excerpt</h2>
                </div>
                <div class="card-body">
                    <textarea name="excerpt" id="excerpt" class="form-control" rows="3"
                              placeholder="A short summary used in listings and search results...">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
            </div>

            {{-- SEO --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">SEO</h2>
                    <span class="badge bg-light text-dark">Search engine preview</span>
                </div>
                <div class="card-body">
                    <div class="p-3 mb-3 rounded" style="background:#f8f9fa;border:1px solid #e5e7eb;">
                        <div class="text-truncate" style="color:#1a0dab;font-size:1.05rem;" id="seo-preview-title">
                            {{ old('seo_title', $meta['seo_title'] ?? $post->title ?: 'Page title') }}
                        </div>
                        <div class="text-truncate" style="color:#006621;font-size:.85rem;">
                            {{ url('/') }}/{{ old('slug', $post->slug) ?: 'your-slug' }}
                        </div>
                        <div style="color:#545454;font-size:.9rem;" id="seo-preview-desc">
                            {{ old('seo_description', $meta['seo_description'] ?? $post->excerpt ?: 'Your meta description will appear here.') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="seo_title" class="form-label">SEO title</label>
                        <input type="text" name="seo_title" id="seo_title" class="form-control" maxlength="255"
                               value="{{ old('seo_title', $meta['seo_title'] ?? '') }}"
                               placeholder="Defaults to the post title">
                    </div>

                    <div class="mb-3">
                        <label for="seo_description" class="form-label">Meta description</label>
                        <textarea name="seo_description" id="seo_description" class="form-control" rows="3" maxlength="500"
                                  placeholder="Defaults to the excerpt">{{ old('seo_description', $meta['seo_description'] ?? '') }}</textarea>
                        <div class="form-text"><span id="seo-desc-count">0</span>/500 characters (aim for 150–160).</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="focus_keyword" class="form-label">Focus keyword</label>
                            <input type="text" name="focus_keyword" id="focus_keyword" class="form-control"
                                   value="{{ old('focus_keyword', $meta['focus_keyword'] ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="robots" class="form-label">Robots</label>
                            <select name="robots" id="robots" class="form-select">
                                @foreach(['index' => 'index, follow', 'noindex' => 'noindex, follow', 'nofollow' => 'index, nofollow'] as $val => $text)
                                    <option value="{{ $val }}" {{ old('robots', $meta['robots'] ?? 'index') === $val ? 'selected' : '' }}>{{ $text }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="canonical_url" class="form-label">Canonical URL</label>
                            <input type="url" name="canonical_url" id="canonical_url" class="form-control"
                                   value="{{ old('canonical_url', $meta['canonical_url'] ?? '') }}" placeholder="https://...">
                        </div>
                        <div class="col-md-6">
                            <label for="og_image" class="form-label">Social share image URL</label>
                            <input type="text" name="og_image" id="og_image" class="form-control"
                                   value="{{ old('og_image', $meta['og_image'] ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h2 class="h5 mb-0">Publish</h2>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            @foreach(['draft' => 'Draft', 'pending' => 'Pending review', 'published' => 'Published', 'private' => 'Private'] as $val => $text)
                                <option value="{{ $val }}" {{ old('status', $post->status ?? 'draft') === $val ? 'selected' : '' }}>{{ $text }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="published_at" class="form-label">Publish date</label>
                        <input type="datetime-local" name="published_at" id="published_at" class="form-control"
                               value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\\TH:i') : '') }}">
                        <div class="form-text">Leave blank to publish immediately.</div>
                    </div>

                    <div class="mb-3">
                        <label for="comment_status" class="form-label">Comments</label>
                        <select name="comment_status" id="comment_status" class="form-select">
                            <option value="open" {{ old('comment_status', $post->comment_status ?? 'open') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="closed" {{ old('comment_status', $post->comment_status ?? 'open') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>{{ $isEdit ? 'Update' : 'Publish' }} {{ $label }}
                    </button>

                    @if($isEdit)
                        @php
                            $live = $post->status === 'published'
                                && (!$post->published_at || $post->published_at->isPast());
                            $publicUrl = $postType === 'page'
                                ? url('/pages/' . $post->slug)
                                : url('/posts/' . $post->slug);
                        @endphp
                        <a href="{{ $live ? $publicUrl : $publicUrl . '?preview=1' }}"
                           target="_blank" rel="noopener" class="btn btn-link w-100 mt-1">
                            {{ $live ? 'View' : 'Preview' }} {{ strtolower($label) }}
                            <i class="fas fa-external-link-alt ms-1"></i>
                        </a>
                    @endif
                </div>
            </div>

            @if($postType === 'post')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent"><h2 class="h5 mb-0">Categories</h2></div>
                    <div class="card-body" style="max-height:220px;overflow:auto;">
                        @forelse($categories as $category)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="categories[]"
                                       id="category-{{ $category->id }}" value="{{ $category->id }}"
                                       {{ ($post->exists && $post->categories->contains($category->id)) || in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="category-{{ $category->id }}">{{ $category->name }}</label>
                            </div>
                        @empty
                            <p class="text-muted mb-0 small">No categories yet. <a href="{{ url('/admin/categories') }}">Add one</a>.</p>
                        @endforelse
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent"><h2 class="h5 mb-0">Tags</h2></div>
                    <div class="card-body" style="max-height:220px;overflow:auto;">
                        @forelse($tags as $tag)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="tags[]"
                                       id="tag-{{ $tag->id }}" value="{{ $tag->id }}"
                                       {{ ($post->exists && $post->tags->contains($tag->id)) || in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="tag-{{ $tag->id }}">{{ $tag->name }}</label>
                            </div>
                        @empty
                            <p class="text-muted mb-0 small">No tags yet. <a href="{{ url('/admin/tags') }}">Add one</a>.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent"><h2 class="h5 mb-0">Featured image</h2></div>
                <div class="card-body">
                    <label for="featured_image" class="form-label small fw-semibold">Image URL or media path</label>
                    <div class="input-group">
                        <input type="text" name="featured_image" id="featured_image" class="form-control"
                               value="{{ old('featured_image', $post->featured_image) }}" placeholder="Image URL or path">
                        <button type="button" class="btn btn-outline-secondary" id="featured-media-choose"
                                data-bs-toggle="modal" data-bs-target="#mediaPickerModal">
                            Choose
                        </button>
                    </div>
                    <div class="form-text">Pick an image from the library, or paste a URL / path such as <code>media/photo.jpg</code>.</div>
                    <img id="featured-preview" src="{{ old('featured_image', $post->featured_image) }}" alt=""
                         class="img-fluid rounded mt-3 {{ old('featured_image', $post->featured_image) ? '' : 'd-none' }}">
                </div>
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="mediaPickerModal" tabindex="-1" aria-labelledby="mediaPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediaPickerModalLabel">Choose an image</h5>
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
                <a href="{{ url('/admin/media') }}" class="btn btn-link me-auto">Manage media</a>
                <button type="button" class="btn btn-outline-secondary" id="media-picker-external">Use external URL</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
<style>
    #quill-editor { min-height: 420px; background: #fff; }
    .ql-toolbar.ql-snow {
        border: 1px solid #d1d5db; border-bottom: none;
        border-radius: 0.5rem 0.5rem 0 0; background: #f9fafb;
        padding: .75rem .5rem .5rem; position: sticky; top: 0; z-index: 5;
    }
    .ql-container.ql-snow {
        border: 1px solid #d1d5db; border-radius: 0 0 0.5rem 0.5rem;
        background: #fff; font-size: 1.05rem;
    }
    .ql-editor { min-height: 360px; padding: 1.5rem; line-height: 1.7; }
    .ql-editor.ql-blank::before { color: #9ca3af; font-style: normal; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hidden = document.getElementById('content');

    // The media picker serves two targets: the featured image field and the
    // editor body. Track which one opened it.
    let pickerTarget = 'featured';
    let pickerIndex = 0;

    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: {
                container: [
                    [{ header: [1, 2, 3, 4, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['blockquote', 'code-block'],
                    [{ align: [] }],
                    ['link', 'image', 'video'],
                    ['clean']
                ],
                handlers: {
                    image: function () {
                        const range = quill.getSelection(true);
                        pickerIndex = range ? range.index : quill.getLength();
                        pickerTarget = 'content';

                        const modalEl = document.getElementById('mediaPickerModal');
                        if (modalEl && window.bootstrap) {
                            bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        }
                    }
                }
            }
        },
        placeholder: 'Compose your content...'
    });

    if (hidden && hidden.value.trim() !== '') {
        quill.root.innerHTML = hidden.value;
    }

    const form = document.getElementById('post-form');
    if (form) {
        form.addEventListener('submit', function () {
            if (hidden) hidden.value = quill.root.innerHTML;
        });
    }

    // Auto-generate slug from title (only for new content).
    const title = document.getElementById('title');
    const slug = document.getElementById('slug');
    if (title && slug && !@json($isEdit)) {
        let touched = false;
        slug.addEventListener('input', () => { touched = true; });
        title.addEventListener('input', () => {
            if (!touched) {
                slug.value = title.value.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });
    }

    // Live SEO preview + character counter.
    const seoTitle = document.getElementById('seo_title');
    const seoDesc = document.getElementById('seo_description');
    const previewTitle = document.getElementById('seo-preview-title');
    const previewDesc = document.getElementById('seo-preview-desc');
    const counter = document.getElementById('seo-desc-count');
    const updateSeo = () => {
        if (previewTitle) previewTitle.textContent = (seoTitle && seoTitle.value) || (title && title.value) || 'Page title';
        if (previewDesc) previewDesc.textContent = (seoDesc && seoDesc.value) || 'Your meta description will appear here.';
        if (counter) counter.textContent = seoDesc ? seoDesc.value.length : 0;
    };
    [seoTitle, seoDesc, title].forEach(el => el && el.addEventListener('input', updateSeo));
    updateSeo();

    // Featured image preview.
    const featured = document.getElementById('featured_image');
    const preview = document.getElementById('featured-preview');

    const resolveImage = (value) => {
        if (!value) return '';
        if (/^(https?:)?\/\//i.test(value) || value.startsWith('/')) return value;
        return '/storage/' + value.replace(/^\/+/, '');
    };

    const syncPreview = () => {
        if (!featured || !preview) return;

        const src = resolveImage(featured.value.trim());
        if (src) {
            preview.src = src;
            preview.classList.remove('d-none');
        } else {
            preview.classList.add('d-none');
        }
    };

    if (featured && preview) {
        featured.addEventListener('input', syncPreview);
        syncPreview();
    }

    // Media picker modal.
    const pickerModal = document.getElementById('mediaPickerModal');
    const pickerGrid = document.getElementById('media-picker-grid');
    const pickerSearch = document.getElementById('media-picker-search');
    let pickerLoaded = false;
    let pickerTimer = null;

    const renderPicker = (items) => {
        if (!pickerGrid) return;

        if (!items.length) {
            pickerGrid.innerHTML = '<div class="col-12 text-muted small">No images found. <a href="{{ url('/admin/media') }}">Upload one</a>.</div>';
            return;
        }

        pickerGrid.innerHTML = items.map((item) => `
            <div class="col-6 col-md-4">
                <button type="button" class="btn p-0 border rounded w-100 media-pick"
                        data-path="${item.path.replace(/"/g, '&quot;')}"
                        data-url="${item.url.replace(/"/g, '&quot;')}"
                        data-alt="${(item.alt || '').replace(/"/g, '&quot;')}">
                    <img src="${item.url}" alt="${(item.alt || item.name).replace(/"/g, '&quot;')}"
                         class="img-fluid rounded-top" style="height:110px;width:100%;object-fit:cover;">
                    <span class="d-block small text-truncate px-2 py-1">${item.name}</span>
                </button>
            </div>
        `).join('');
    };

    const loadPicker = (search = '') => {
        if (!pickerGrid) return;

        pickerGrid.innerHTML = '<div class="col-12 text-muted small">Loading images…</div>';

        fetch(`{{ route('admin.media.picker') }}?search=${encodeURIComponent(search)}`, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then((response) => response.ok ? response.json() : [])
            .then(renderPicker)
            .catch(() => renderPicker([]));
    };

    if (pickerModal) {
        pickerModal.addEventListener('show.bs.modal', () => {
            if (!pickerLoaded) {
                pickerLoaded = true;
                loadPicker();
            }
        });
    }

    if (pickerSearch) {
        pickerSearch.addEventListener('input', () => {
            clearTimeout(pickerTimer);
            pickerTimer = setTimeout(() => loadPicker(pickerSearch.value.trim()), 250);
        });
    }

    if (pickerGrid) {
        pickerGrid.addEventListener('click', (event) => {
            const button = event.target.closest('.media-pick');
            if (!button) return;

            if (pickerTarget === 'content') {
                const src = resolveImage(button.dataset.path || button.dataset.url || '');
                const alt = (button.dataset.alt || '').replace(/"/g, '&quot;');

                if (src) {
                    quill.clipboard.dangerouslyPasteHTML(
                        pickerIndex,
                        `<img src="${src}" alt="${alt}">`,
                        'user'
                    );
                    quill.setSelection(pickerIndex + 1);
                }
            } else if (featured) {
                featured.value = button.dataset.path;
                syncPreview();
            }

            if (window.bootstrap && pickerModal) {
                bootstrap.Modal.getOrCreateInstance(pickerModal).hide();
            }
        });
    }

    const featuredChoose = document.getElementById('featured-media-choose');
    if (featuredChoose) {
        featuredChoose.addEventListener('click', () => { pickerTarget = 'featured'; });
    }

    const insertExternal = (url) => {
        if (!url) return;

        if (pickerTarget === 'content') {
            quill.insertEmbed(pickerIndex, 'image', url, 'user');
            quill.setSelection(pickerIndex + 1);
        } else if (featured) {
            featured.value = url;
            syncPreview();
        }
    };

    const externalButton = document.getElementById('media-picker-external');
    if (externalButton) {
        externalButton.addEventListener('click', () => {
            const url = prompt('Enter an image URL');
            if (!url) return;

            insertExternal(url);

            if (window.bootstrap && pickerModal) {
                bootstrap.Modal.getOrCreateInstance(pickerModal).hide();
            }
        });
    }
});
</script>
@endpush
