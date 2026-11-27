@extends('admin.layouts.app')

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
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Themes</h1>
            <p class="text-muted mb-0">Manage and activate themes for your site</p>
        </div>
        <div class="mt-3 mt-sm-0">
            <a href="{{ route('admin.settings.index') }}" class="btn btn-primary">
                <i class="fas fa-cog me-2"></i>Customize Theme
            </a>
        </div>
    </div>

    {{-- Site / active theme bar --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="site_id" class="form-label fw-semibold">Site</label>
                    <form action="{{ route('admin.themes.index') }}" method="GET">
                        <select name="site_id" id="site_id" class="form-select" onchange="this.form.submit()">
                            @foreach(\App\Models\Site::all() as $site)
                                <option value="{{ $site->id }}" {{ ($currentSite->id ?? null) == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }} ({{ $site->domain ?? 'No domain' }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="col-md-7">
                    <span class="text-muted small d-block mb-1">Active theme</span>
                    @if($currentSite && $currentSite->theme)
                        <span class="badge bg-primary fs-6">
                            {{ $currentSite->active_theme['name'] ?? ucfirst($currentSite->theme) }}
                        </span>
                        <span class="text-muted small ms-2">{{ $currentSite->theme }}</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6">No theme selected</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Theme cards --}}
    @if(count($themes))
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            @foreach($themes as $theme)
                @php($isActive = $currentSite && $currentSite->theme === ($theme['slug'] ?? null))
                <div class="col">
                    <div class="card theme-card h-100 border-0 shadow-sm overflow-hidden">
                        <div class="theme-preview-container">
                            @if($theme['has_screenshot'] ?? false)
                                <img src="{{ $theme['screenshot_url'] }}" class="theme-preview"
                                     alt="{{ $theme['name'] ?? 'Theme' }} preview" loading="lazy">
                            @else
                                <div class="theme-preview bg-gradient d-flex align-items-center justify-content-center">
                                    <div class="text-center text-white">
                                        <i class="fas fa-palette fa-2x mb-2 opacity-75"></i>
                                        <div class="small opacity-75">No Preview</div>
                                    </div>
                                </div>
                            @endif

                            @if($isActive)
                                <span class="badge bg-success position-absolute" style="top: 0.75rem; right: 0.75rem;">Active</span>
                            @endif
                        </div>

                        <div class="card-body">
                            <h5 class="card-title mb-1">
                                <a href="{{ route('admin.themes.edit', $theme['slug']) }}" class="text-decoration-none">
                                    {{ $theme['name'] ?? 'Untitled Theme' }}
                                </a>
                            </h5>
                            <p class="text-muted small mb-3">{{ $theme['description'] ?? 'No description provided.' }}</p>
                            <div class="d-flex flex-wrap gap-3 text-muted small">
                                <span><strong>Version</strong> {{ $theme['version'] ?? '1.0.0' }}</span>
                                <span><strong>Author</strong> {{ $theme['author'] ?? 'Unknown' }}</span>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.themes.edit', $theme['slug']) }}" class="btn btn-outline-secondary flex-shrink-0">
                                    <i class="fas fa-circle-info"></i>
                                </a>
                                <a href="{{ url('/?theme=' . $theme['slug']) }}" target="_blank" rel="noopener"
                                   class="btn btn-outline-primary flex-shrink-0" title="Preview theme without activating it">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.themes.update', $theme['slug']) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <input type="hidden" name="site_id" value="{{ $currentSite->id ?? '' }}">
                                    <input type="hidden" name="active" value="1">
                                    @if($isActive)
                                        <button type="submit" class="btn btn-outline-success w-100" disabled>
                                            <i class="fas fa-check me-2"></i>Active Theme
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-rocket me-2"></i>Activate Theme
                                        </button>
                                    @endif
                                </form>
                                @if(file_exists(base_path('themes/' . $theme['slug'] . '/demo-content.json')))
                                    <form action="{{ route('admin.themes.demo-content', $theme['slug']) }}" method="POST" class="flex-shrink-0">
                                        @csrf
                                        <input type="hidden" name="site_id" value="{{ $currentSite->id ?? '' }}">
                                        <button type="submit" class="btn btn-outline-secondary" title="Install demo content for this theme">
                                            <i class="fas fa-gift"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">No themes found in the <code>themes/</code> directory.</div>
    @endif
@endsection
