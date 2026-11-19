@extends('admin.layouts.app')

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
                        <div class="mb-2" style="font-size: 2.25rem; line-height: 1;">{{ $card['icon'] }}</div>
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
                    <h2 class="h5 mb-0">Recent Posts</h2>
                    <a href="{{ url('/admin/posts') }}" class="btn btn-sm btn-outline-primary">View all</a>
                </div>
                <div class="card-body p-0">
                    @if(count($recent_posts) > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recent_posts as $post)
                                <div class="list-group-item d-flex justify-content-between align-items-center gap-3">
                                    <div class="text-truncate">
                                        <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}"
                                           class="fw-semibold text-decoration-none d-block text-truncate"
                                           style="color: var(--primary-color);">
                                            {{ $post->title }}
                                        </a>
                                        <small class="text-muted">
                                            {{ ucfirst($post->status) }}
                                            &middot; {{ $post->user->name ?? 'Author' }}
                                            &middot; {{ $post->updated_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" class="btn btn-sm btn-primary flex-shrink-0">Edit</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center py-4 mb-0">
                            No posts yet.
                            <a href="{{ url('/admin/posts/create') }}" style="color: var(--primary-color);">Create one</a>!
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
@endsection
