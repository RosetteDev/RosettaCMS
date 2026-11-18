@extends('admin.layouts.app')

@php($slug = $theme['slug'] ?? '')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.themes.index') }}">Themes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $theme['name'] ?? $slug }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1">{{ $theme['name'] ?? 'Theme' }}</h1>
            <p class="text-muted mb-0">{{ $theme['description'] ?? 'No description provided.' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($isActive)
                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                    <i class="fas fa-external-link-alt me-2"></i>View Site
                </a>
            @endif
            <a href="{{ route('admin.themes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>All Themes
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                @if($theme['has_screenshot'] ?? false)
                    <img src="{{ $theme['screenshot_url'] }}" class="w-100" alt="{{ $theme['name'] ?? 'Theme' }} preview">
                @else
                    <div class="bg-dark d-flex align-items-center justify-content-center text-white" style="min-height: 280px;">
                        <div class="text-center opacity-75">
                            <i class="fas fa-image fa-3x mb-2"></i>
                            <div>No screenshot available</div>
                        </div>
                    </div>
                @endif
            </div>

            @if(!empty($theme['settings']) && is_array($theme['settings']))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Default Palette</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($theme['settings'] as $key => $value)
                                <div class="col-sm-6">
                                    <div class="text-muted small text-uppercase">{{ str_replace('_', ' ', $key) }}</div>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        @if(str_contains($key, 'color') && preg_match('/^#[0-9a-f]{3,8}$/i', $value))
                                            <span class="border rounded" style="width: 1.25rem; height: 1.25rem; background: {{ $value }};"></span>
                                        @endif
                                        <code class="small">{{ $value }}</code>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-muted small mb-0 mt-3">
                            Palette values can be overridden per site in
                            <a href="{{ route('admin.settings.index') }}">Settings</a>.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="text-muted small text-uppercase">Status</div>
                            @if($isActive)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="text-muted small text-uppercase">Version</div>
                            <strong>{{ $theme['version'] ?? '1.0.0' }}</strong>
                        </div>
                    </div>

                    <dl class="row mb-0 small">
                        <dt class="col-4 text-muted">Slug</dt>
                        <dd class="col-8"><code>{{ $theme['slug'] ?? $slug }}</code></dd>

                        <dt class="col-4 text-muted">Author</dt>
                        <dd class="col-8">
                            @if(!empty($theme['author_url']))
                                <a href="{{ $theme['author_url'] }}" target="_blank" rel="noopener">{{ $theme['author'] ?? 'Unknown' }}</a>
                            @else
                                {{ $theme['author'] ?? 'Unknown' }}
                            @endif
                        </dd>

                        <dt class="col-4 text-muted">Site</dt>
                        <dd class="col-8">{{ $currentSite->name ?? 'No site' }}</dd>
                    </dl>
                </div>
                <div class="card-footer bg-white border-top">
                    @if($isActive)
                        <button class="btn btn-outline-success w-100" disabled>
                            <i class="fas fa-check me-2"></i>Currently Active
                        </button>
                    @elseif(!$renderable)
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-ban me-2"></i>Not Ready to Activate
                        </button>
                        <p class="text-muted small mb-0 mt-2">
                            This theme is missing the view files needed to render the public site.
                        </p>
                    @elseif($currentSite)
                        <form action="{{ route('admin.themes.update', $slug) }}" method="POST">
                            @csrf
                            <input type="hidden" name="site_id" value="{{ $currentSite->id }}">
                            <input type="hidden" name="active" value="1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-rocket me-2"></i>Activate for {{ $currentSite->name }}
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning mb-0 small">Create a site before activating a theme.</div>
                    @endif
                </div>
            </div>

            @if(!empty($theme['supports']['features']))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Supports</h5></div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($theme['supports']['features'] as $feature)
                                <span class="badge bg-light text-dark border">{{ $feature }}</span>
                            @endforeach
                        </div>
                        @if(!empty($theme['supports']['post_types']))
                            <div class="text-muted small mt-3">
                                Post types:
                                @foreach($theme['supports']['post_types'] as $type)
                                    <code>{{ $type }}</code>@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if(!empty($theme['assets']))
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h5 class="mb-0">Assets</h5></div>
                    <ul class="list-group list-group-flush">
                        @foreach(($theme['assets']['css'] ?? []) as $asset)
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-paint-brush text-muted me-2"></i>{{ $asset }}</span>
                                <span class="badge bg-light text-dark border">CSS</span>
                            </li>
                        @endforeach
                        @foreach(($theme['assets']['js'] ?? []) as $asset)
                            <li class="list-group-item d-flex justify-content-between">
                                <span><i class="fas fa-code text-muted me-2"></i>{{ $asset }}</span>
                                <span class="badge bg-light text-dark border">JS</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
@endsection
