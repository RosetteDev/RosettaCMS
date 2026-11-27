@extends('admin.layouts.app')

@section('title', $site->name)

@push('styles')
<style>
    @media (max-width: 640px) {
        .dl-grid { grid-template-columns: 1fr !important; }
    }
</style>
@endpush

@section('content')
    <div class="hero">
        <div class="container">
            <h1>{{ $site->name }}</h1>
            <p>{{ $site->domain ?: $site->slug }}</p>
        </div>
    </div>

    <div class="container" style="max-width: 900px; padding: 2rem;">
        <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-primary">Edit</a>
                <a href="{{ url('/admin/sites') }}" class="btn btn-outline">Back to sites</a>
            </div>

            <dl class="dl-grid" style="display: grid; grid-template-columns: 180px 1fr; gap: 0.75rem 1rem; color: var(--text-color);">
                <dt style="font-weight: 600;">Name</dt>
                <dd>{{ $site->name }}</dd>

                <dt style="font-weight: 600;">Slug</dt>
                <dd><code>{{ $site->slug }}</code></dd>

                <dt style="font-weight: 600;">Domain</dt>
                <dd>{{ $site->domain ?: '—' }}</dd>

                <dt style="font-weight: 600;">Description</dt>
                <dd>{{ $site->description ?: '—' }}</dd>

                <dt style="font-weight: 600;">Locale</dt>
                <dd>{{ $site->locale ?: '—' }}</dd>

                <dt style="font-weight: 600;">Default Language</dt>
                <dd>{{ $site->defaultLanguage ? ($site->defaultLanguage->flag . ' ' . $site->defaultLanguage->name) : 'Global default' }}</dd>

                <dt style="font-weight: 600;">Timezone</dt>
                <dd>{{ $site->timezone ?: '—' }}</dd>

                <dt style="font-weight: 600;">Theme</dt>
                <dd>{{ $site->active_theme['name'] ?? ($site->theme ?: 'rosetta') }}</dd>

                <dt style="font-weight: 600;">Homepage</dt>
                <dd>{{ $site->homepage->title ?? 'Latest posts' }}</dd>

                <dt style="font-weight: 600;">Status</dt>
                <dd>{{ $site->is_active ? 'Active' : 'Inactive' }}</dd>

                <dt style="font-weight: 600;">Posts</dt>
                <dd>{{ $site->posts()->count() }}</dd>

                <dt style="font-weight: 600;">Media</dt>
                <dd>{{ $site->media()->count() }}</dd>

                <dt style="font-weight: 600;">Created</dt>
                <dd>{{ $site->created_at?->format('M j, Y g:i a') }}</dd>
            </dl>
        </div>
    </div>
@endsection
