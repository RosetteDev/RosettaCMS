@extends('admin.layouts.app')

@push('styles')
@php
    use App\Models\CustomWidget;
@endphp
<style>
    .widget-area {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
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
</style>
@endpush

@section('content')
    {{-- Page heading --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Welcome back! Here's what's happening with your content.</p>
        </div>
        <div class="mt-3 mt-sm-0">
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
                        <div class="mb-2" style="font-size: 2.25rem; line-height: 1%;">{{ $card['icon'] }}</div>
                        <h3 class="mb-1 fw-bold" style="color: {{ $card['color'] }};">
                            {{ $stats[$card['key']] ?? 0 }}
                        </h3>
                        <p class="text-muted mb-0 small">{{ $card['label'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        {{-- Recent posts --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Recent Content</h2>
                    <a href="{{ url('/admin/posts') }}" class="btn btn-sm btn-outline-primary">View all</a>
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
        </div>

        {{-- Quick actions --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h2 class="h5 mb-0">Quick Actions</h2>
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
        </div>
    </div>
    
    <div class="row g-4">
        {{-- Recent media --}}
        @if(count($recentMedia) > 0)
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h2 class="h5 mb-0">Recent Media</h2>
                    <a href="{{ url('/admin/media') }}" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($recentMedia as $media)
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="media-card" style="position: relative; border-radius: 8px; overflow: hidden;">
                                    @if(str_starts_with($media->mime_type, 'image/'))
                                        <img src="{{ $media->url }}" alt="{{ $media->alt_text }}" style="width: 100%; height: 80px; object-fit: cover;">
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
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    // Widget management
    document.addEventListener('DOMContentLoaded', function() {
        // Add widget drag-and-drop functionality
        const widgets = document.querySelectorAll('.widget');
        
        widgets.forEach(widget => {
            widget.addEventListener('click', function(e) {
                if (e.target.closest('.widget-actions')) return;
                
                // Widget click handler
                console.log('Widget clicked:', widget.dataset.widgetId);
            });
        });
    });
</script>
@endpush
