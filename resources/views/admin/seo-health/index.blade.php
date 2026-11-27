@extends('admin.layouts.app')

@section('title', 'SEO Health')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">SEO Health</h1>
    <p class="text-muted">One-click checks and fixes for the current site.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if(!$site)
    <div class="alert alert-warning">No site could be resolved for this request.</div>
@else
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="h5 mb-0">Overall</h2>
                <span class="badge {{ $allOk ? 'bg-success' : 'bg-warning text-dark' }} fs-6">
                    {{ $allOk ? 'Healthy' : 'Needs attention' }}
                </span>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 mb-3">Checks</h2>
            <ul class="list-unstyled">
                @foreach($checks as $key => $check)
                    <li class="d-flex align-items-start justify-content-between gap-3 py-2 border-bottom">
                        <div class="d-flex align-items-start gap-2">
                            <span class="badge {{ $check['ok'] ? 'bg-success' : 'bg-danger' }}">
                                {{ $check['ok'] ? 'OK' : 'FAIL' }}
                            </span>
                            <div>
                                <div class="fw-semibold">{{ $check['label'] }}</div>
                                <div class="text-muted small">{{ $check['detail'] }}</div>
                            </div>
                        </div>
                        @if(!$check['ok'] && !empty($check['fix']))
                            <form method="POST" action="{{ route('admin.seo-health.fix', $check['fix']) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary">Fix</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @if(count($brokenLinks['broken']) > 0)
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Broken internal links ({{ count($brokenLinks['broken']) }})</h2>
                <div class="table-responsive"><table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Post / page</th>
                            <th>Broken URL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brokenLinks['broken'] as $link)
                            <tr>
                                <td>
                                    <a href="{{ $link['post_url'] }}" target="_blank" rel="noopener">{{ $link['post_title'] }}</a>
                                </td>
                                <td><code>{{ $link['url'] }}</code></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            </div>
        </div>
    @endif
@endif
@endsection
