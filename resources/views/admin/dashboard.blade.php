@extends('admin.layouts.app')

@push('styles')
@php
    use App\Models\CustomWidget;
@endphp
<style>
    .widget-area {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(300px, 100%), 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .widget {
        background-color: var(--light-color);
        border-radius: 8px;
        border: 1px solid var(--border-color);
        transition: all 0.2s;
    }
    
    .widget:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .widget-header {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .widget-header h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .widget-body {
        padding: 1rem;
    }
    
    .widget-actions .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }

    /* --- Dashboard layout editor --- */
    .dashboard-sections {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 1.5rem;
        align-items: start;
        margin-bottom: 1.5rem;
    }
    .dashboard-section { min-width: 0; }
    .section-recent-content { grid-column: span 8; }
    .section-quick-actions { grid-column: span 4; }
    .section-recent-media { grid-column: span 12; }

    @media (max-width: 991px) {
        .section-recent-content, .section-quick-actions, .section-recent-media { grid-column: span 12; }
    }

    body.layout-editing .dashboard-section {
        border: 2px dashed #93c5fd;
        border-radius: 10px;
        padding: 0.35rem;
        transition: box-shadow 0.15s;
    }
    body.layout-editing .dashboard-section:hover {
        box-shadow: 0 4px 14px rgba(59, 130, 246, 0.18);
    }
    .section-drag-handle {
        display: none;
        color: #6b7280;
        cursor: grab;
        font-size: 1rem;
        line-height: 1;
    }
    body.layout-editing .section-drag-handle { display: inline-block; }
    .section-move { display: none; padding: 0.2rem 0.45rem; font-size: 0.75rem; }
    body.layout-editing .section-move { display: inline-block; }
    .dashboard-section.drag-over { box-shadow: 0 0 0 3px #3b82f6; }
    .dashboard-section.dragging { opacity: 0.4; }
</style>
@endpush

@section('content')
    {{-- Page heading --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Welcome back! Here's what's happening with your content.</p>
        </div>
        <div class="d-flex gap-2 align-items-center mt-3 mt-sm-0">
            <button type="button" id="customizeLayoutBtn" class="btn btn-outline-secondary btn-sm" aria-pressed="false">
                <i class="fas fa-arrows-alt me-1"></i>Customize layout
            </button>
            <button type="button" id="saveLayoutBtn" class="btn btn-primary btn-sm" hidden>
                <i class="fas fa-check me-1"></i>Save layout
            </button>
            <a href="{{ url('/admin/posts/create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>New Post
            </a>
        </div>
    </div>

    {{-- Custom Widgets Area --}}
    <div class="widget-area" id="customWidgets">
        @if(isset($widgets) && is_array($widgets))
            @foreach($widgets as $widgetId => $widgetConfig)
                @if($widgetConfig['status'] ?? 'enabled' === 'enabled')
                    @php
                        $widget = \App\Models\CustomWidget::find($widgetId);
                    @endphp
                    
                    @if($widget && $widget->enabled)
                        <div class="widget" data-widget-id="{{ $widget->id }}">
                            <div class="widget-header">
                                <h3>{{ $widget->name }}</h3>
                                <div class="widget-actions">
                                    <a href="{{ route('admin.widgets.edit', $widget->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="widget-body">
                                @if($widget->type === 'html')
                                    {!! $widget->content !!}
                                @elseif($widget->type === 'rss')
                                    <div class="rss-widget">
                                        <p class="text-muted small">RSS feed content would be loaded here</p>
                                    </div>
                                @elseif($widget->type === 'custom')
                                    @php
                                        // Allow custom widget rendering via event or hook
                                        echo $widget->content ?? 'Custom widget content';
                                    @endphp
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            @endforeach
        @endif
    </div>

    {{-- Stat cards --}}
    @php
        $statCards = [
            ['key' => 'total_posts',      'label' => 'Total Posts',         'icon' => '📝', 'color' => 'var(--primary-color)'],
            ['key' => 'total_pages',      'label' => 'Total Pages',         'icon' => '📄', 'color' => 'var(--primary-color)'],
            ['key' => 'total_media',      'label' => 'Media Files',         'icon' => '🖼️', 'color' => 'var(--primary-color)'],
            ['key' => 'total_sites',      'label' => 'Sites',               'icon' => '🌐', 'color' => 'var(--primary-color)'],
            ['key' => 'total_users',      'label' => 'Users',               'icon' => '👥', 'color' => 'var(--primary-color)'],
            ['key' => 'active_users',     'label' => 'Active Users',        'icon' => '✅', 'color' => '#10b981'],
            ['key' => 'pending_users',    'label' => 'Pending Verification','icon' => '⏳', 'color' => '#f59e0b'],
            ['key' => 'total_comments',   'label' => 'Total Comments',      'icon' => '💬', 'color' => 'var(--primary-color)'],
            ['key' => 'pending_comments', 'label' => 'Pending Comments',    'icon' => '🕓', 'color' => '#f59e0b'],
        ];
    @endphp

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4 mb-5">
        @foreach($statCards as $card)
            <div class="col">
                <div class="card h-100 border-0 shadow-sm text-center">
                    <div class="card-body py-4">
                        <div class="mb-2" style="font-size: 2.25rem; line-height: 1.2;">{{ $card['icon'] }}</div>
                        <h3 class="mb-1 fw-bold" style="color: {{ $card['color'] }};">
                            {{ $stats[$card['key']] ?? 0 }}
                        </h3>
                        <p class="text-muted mb-0 small">{{ $card['label'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Query profiler --}}
    <div class="card border-0 shadow-sm mb-4" id="queryProfilerCard">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0">Query Profiler</h2>
            <span class="badge bg-secondary">{{ $queryCount }} queries · {{ number_format($totalQueryTime, 1) }} ms total</span>
        </div>
        <div class="card-body">
            @if(count($slowQueries) > 0)
                <div class="list-group list-group-flush">
                    @foreach($slowQueries as $q)
                        <div class="list-group-item bg-transparent d-flex justify-content-between align-items-start gap-3">
                            <code class="text-truncate flex-grow-1" style="max-width: 70%;" title="{{ $q['sql'] }}">{{ $q['sql'] }}</code>
                            <span class="badge {{ $q['time_ms'] > 50 ? 'bg-danger' : ($q['time_ms'] > 10 ? 'bg-warning text-dark' : 'bg-success') }}">{{ $q['time_ms'] }} ms</span>
                        </div>
                    @endforeach
                </div>
                @if($queryCount > 5)
                    <small class="text-muted d-block mt-2">Showing the {{ count($slowQueries) }} slowest of {{ $queryCount }} queries on this page.</small>
                @endif
            @else
                <p class="text-muted mb-0">No database queries recorded.</p>
            @endif
        </div>
    </div>

    @php
        $layoutIndex = array_flip($layout);
    @endphp
    <div id="dashboardSections" class="dashboard-sections">
        <section class="dashboard-section section-recent-content" data-section="recent-content" style="order: {{ $layoutIndex['recent-content'] ?? 0 }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Recent Content</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-drag-handle" aria-hidden="true"><i class="fas fa-grip-vertical"></i></span>
                        <button type="button" class="section-move btn btn-sm btn-outline-secondary section-move-up" aria-label="Move Recent Content up"><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="section-move btn btn-sm btn-outline-secondary section-move-down" aria-label="Move Recent Content down"><i class="fas fa-arrow-down"></i></button>
                        <a href="{{ url('/admin/posts') }}" class="btn btn-sm btn-outline-primary">View all</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($recentPosts) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentPosts as $post)
                                @php
                                    $editUrl = url('/admin/' . ($post->post_type === 'page' ? 'pages' : 'posts') . '/' . $post->id . '/edit');
                                @endphp
                                <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                                    <div class="text-truncate">
                                        <a href="{{ $editUrl }}"
                                           class="fw-semibold text-decoration-none d-block text-truncate"
                                           style="color: var(--primary-color);">
                                            {{ $post->title }}
                                        </a>
                                        <small class="text-muted">
                                            {{ ucfirst($post->post_type) }}
                                            &middot; {{ ucfirst($post->status) }}
                                            &middot; {{ $post->user->name ?? 'Author' }}
                                            &middot; {{ $post->updated_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <a href="{{ $editUrl }}" class="btn btn-sm btn-primary flex-shrink-0">Edit</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">
                            No content yet.
                            <a href="{{ url('/admin/posts/create') }}" style="color: var(--primary-color);">Create a post</a>!
                        </p>
                    @endif
                </div>
            </div>
        </section>

        <section class="dashboard-section section-quick-actions" data-section="quick-actions" style="order: {{ $layoutIndex['quick-actions'] ?? 1 }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Quick Actions</h2>
                    <div class="section-controls">
                        <span class="section-drag-handle" aria-hidden="true"><i class="fas fa-grip-vertical"></i></span>
                        <button type="button" class="section-move btn btn-sm btn-outline-secondary section-move-up" aria-label="Move Quick Actions up"><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="section-move btn btn-sm btn-outline-secondary section-move-down" aria-label="Move Quick Actions down"><i class="fas fa-arrow-down"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ url('/admin/posts/create') }}" class="btn btn-outline-primary text-start">
                            <span class="me-1">📝</span> New Post
                        </a>
                        <a href="{{ url('/admin/pages/create') }}" class="btn btn-outline-primary text-start">
                            <span class="me-1">📄</span> New Page
                        </a>
                        <a href="{{ url('/admin/media') }}" class="btn btn-outline-primary text-start">
                            <span class="me-1">🖼️</span> Upload Media
                        </a>
                        @if(auth()->user()?->hasRole('admin'))
                        <a href="{{ url('/admin/widgets') }}" class="btn btn-outline-primary text-start">
                            <span class="me-1">🧩</span> Manage Widgets
                        </a>
                        <a href="{{ url('/admin/themes') }}" class="btn btn-outline-primary text-start">
                            <span class="me-1">🎨</span> Manage Themes
                        </a>
                        <a href="{{ url('/admin/settings') }}" class="btn btn-outline-secondary text-start">
                            <span class="me-1">⚙️</span> Settings
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-section section-recent-media" data-section="recent-media" style="order: {{ $layoutIndex['recent-media'] ?? 2 }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Recent Media</h2>
                    <div class="d-flex align-items-center gap-2">
                        <span class="section-drag-handle" aria-hidden="true"><i class="fas fa-grip-vertical"></i></span>
                        <button type="button" class="section-move btn btn-sm btn-outline-secondary section-move-up" aria-label="Move Recent Media up"><i class="fas fa-arrow-up"></i></button>
                        <button type="button" class="section-move btn btn-sm btn-outline-secondary section-move-down" aria-label="Move Recent Media down"><i class="fas fa-arrow-down"></i></button>
                        <a href="{{ url('/admin/media') }}" class="btn btn-sm btn-outline-primary">View all</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($recentMedia as $media)
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="media-card" style="position: relative; border-radius: 8px; overflow: hidden;">
                                    @if(str_starts_with($media->mime_type, 'image/'))
                                        <img src="{{ $media->url }}" alt="{{ $media->alt_text }}" loading="lazy" decoding="async" style="width: 100%; height: 80px; object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center" style="width: 100%; height: 80px; background-color: var(--light-color);">
                                            <i class="fas fa-file fa-2x text-muted"></i>
                                        </div>
                                    @endif
                                    <div class="media-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;">
                                        <a href="{{ url('/admin/media/' . $media->id) }}" class="btn btn-sm btn-white">View</a>
                                    </div>
                                </div>
                                <div class="mt-2 text-truncate small" style="font-size: 0.75rem;">{{ $media->filename }}</div>
                            </div>
                        @endforeach
                    </div>
                    @if(count($recentMedia) === 0)
                        <p class="text-muted text-center py-3 mb-0">No media yet. <a href="{{ url('/admin/media') }}" style="color: var(--primary-color);">Upload a file</a>.</p>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('dashboardSections');
        if (!container) return;

        const customizeBtn = document.getElementById('customizeLayoutBtn');
        const saveBtn = document.getElementById('saveLayoutBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const sections = () => Array.from(container.querySelectorAll('.dashboard-section'));

        // --- Customize toggle ---
        customizeBtn.addEventListener('click', function() {
            const editing = document.body.classList.toggle('layout-editing');
            customizeBtn.setAttribute('aria-pressed', editing ? 'true' : 'false');
            customizeBtn.innerHTML = editing
                ? '<i class="fas fa-times me-1"></i>Cancel'
                : '<i class="fas fa-arrows-alt me-1"></i>Customize layout';
            saveBtn.hidden = !editing;
            sections().forEach(s => s.draggable = editing);
        });

        // --- Save layout ---
        saveBtn.addEventListener('click', async function() {
            const order = sections().map(s => s.dataset.section);
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving…';
            try {
                const response = await fetch('{{ route('admin.dashboard.layout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ order: order }),
                });
                if (!response.ok) throw new Error('Save failed');
                document.body.classList.remove('layout-editing');
                customizeBtn.setAttribute('aria-pressed', 'false');
                customizeBtn.innerHTML = '<i class="fas fa-arrows-alt me-1"></i>Customize layout';
                saveBtn.hidden = true;
                sections().forEach(s => s.draggable = false);
            } catch (e) {
                alert('Could not save the dashboard layout. Please try again.');
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-check me-1"></i>Save layout';
            }
        });

        // --- Keep the inline `order` style in sync with the DOM ---
        function syncOrder() {
            sections().forEach((s, i) => { s.style.order = i; });
        }

        // --- HTML5 drag & drop ---
        let dragSource = null;
        sections().forEach(section => {
            section.addEventListener('dragstart', function() {
                dragSource = section;
                section.classList.add('dragging');
            });
            section.addEventListener('dragend', function() {
                dragSource = null;
                section.classList.remove('dragging');
                sections().forEach(s => s.classList.remove('drag-over'));
            });
            section.addEventListener('dragover', function(e) {
                e.preventDefault();
                sections().forEach(s => s.classList.remove('drag-over'));
                if (section !== dragSource) section.classList.add('drag-over');
            });
            section.addEventListener('drop', function(e) {
                e.preventDefault();
                if (!dragSource || section === dragSource) return;
                const target = sections().indexOf(section);
                const source = sections().indexOf(dragSource);
                if (target > source) {
                    container.insertBefore(dragSource, section.nextElementSibling);
                } else {
                    container.insertBefore(dragSource, section);
                }
                section.classList.remove('drag-over');
                syncOrder();
            });
        });

        // --- Keyboard move (up/down buttons) ---
        sections().forEach(section => {
            section.querySelector('.section-move-up')?.addEventListener('click', function() {
                const prev = section.previousElementSibling;
                if (prev) container.insertBefore(section, prev);
                syncOrder();
            });
            section.querySelector('.section-move-down')?.addEventListener('click', function() {
                const next = section.nextElementSibling;
                if (next) container.insertBefore(next, section);
                syncOrder();
            });
        });
    });
</script>
@endpush
