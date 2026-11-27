@extends('admin.layouts.app')

@push('styles')
<style>
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-card { background: var(--light-color); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.25rem; }
    .stat-card .stat-value { font-size: 1.9rem; font-weight: 700; color: var(--text-color); line-height: 1.1; }
    .stat-card .stat-label { font-size: 0.8rem; color: var(--secondary-color); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.35rem; }
    .panel { background: var(--light-color); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem; }
    .panel h3 { font-size: 1.05rem; margin: 0 0 1rem; color: var(--text-color); }
    .panel table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
    .panel table th { text-align: left; padding: 0.5rem 0.4rem; border-bottom: 2px solid var(--border-color); color: var(--secondary-color); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; }
    .panel table td { padding: 0.5rem 0.4rem; border-bottom: 1px solid var(--border-color); color: var(--text-color); }
    .bar-chart { display: flex; align-items: flex-end; gap: 4px; height: 140px; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 4px; min-width: 0; }
    .bar-fill { width: 100%; background: var(--primary-color, #0f766e); border-radius: 4px 4px 0 0; min-height: 2px; }
    .bar-label { font-size: 0.6rem; color: var(--secondary-color); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    .empty-note { color: var(--secondary-color); text-align: center; padding: 2rem 0; }
    .range-links a { text-decoration: none; padding: 0.4rem 0.9rem; border-radius: 6px; font-size: 0.85rem; margin-right: 0.25rem; }
    .range-links a.active { background: var(--primary-color, #0f766e); color: #fff; }
    .range-links a:not(.active) { color: var(--text-color); border: 1px solid var(--border-color); }
</style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Analytics</h1>
            <p class="text-muted mb-0">Traffic overview for your site — pageviews, top content, referrers and devices.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <form method="GET" action="{{ route('admin.analytics.index') }}" class="range-links d-flex align-items-center">
                @foreach([7, 30, 90] as $range)
                    <a href="{{ route('admin.analytics.index', ['days' => $range]) }}" class="{{ $days === $range ? 'active' : '' }}">{{ $range }}d</a>
                @endforeach
            </form>
            <a href="{{ route('admin.analytics.export', ['days' => $days]) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-download me-1"></i>Export CSV
            </a>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-value">{{ number_format($totalViews) }}</div><div class="stat-label">Pageviews ({{ $days }}d)</div></div>
        <div class="stat-card"><div class="stat-value">{{ number_format($uniqueVisitors) }}</div><div class="stat-label">Unique visitors</div></div>
        <div class="stat-card"><div class="stat-value">{{ number_format($topPosts->count()) }}</div><div class="stat-label">Posts with traffic</div></div>
        <div class="stat-card"><div class="stat-value">{{ number_format($topReferrers->count()) }}</div><div class="stat-label">Referrer hosts</div></div>
    </div>

    <div class="panel">
        <h3>Daily pageviews</h3>
        @if($totalViews === 0)
            <div class="empty-note">No pageviews recorded yet. Visitors will be tracked automatically once the site is live.</div>
        @else
            <div class="bar-chart">
                @foreach($daily as $date => $count)
                    <div class="bar-col" title="{{ $date }}: {{ $count }} views">
                        <div class="bar-fill" style="height: {{ $count > 0 ? max(8, round($count / $maxDaily * 100)) : 2 }}%;"></div>
                        <div class="bar-label">{{ substr($date, 5) }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="row" style="display:flex; gap:1.5rem; flex-wrap:wrap;">
        <div style="flex: 1 1 340px;">
            <div class="panel">
                <h3>Top posts</h3>
                @if($topPosts->isEmpty())
                    <div class="empty-note">No post traffic yet.</div>
                @else
                    <div class="table-responsive"><table>
                        <thead><tr><th>Post</th><th>Views</th></tr></thead>
                        <tbody>
                            @foreach($topPosts as $row)
                                <tr>
                                    <td>{{ $row['slug'] ? '<a href="' . url('/posts/' . $row['slug']) . '" target="_blank">' . e($row['title']) . '</a>' : e($row['title']) }}</td>
                                    <td><strong>{{ number_format($row['views']) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="panel">
                <h3>Top pages</h3>
                @if($topPaths->isEmpty())
                    <div class="empty-note">No page traffic yet.</div>
                @else
                    <div class="table-responsive"><table>
                        <thead><tr><th>Path</th><th>Views</th></tr></thead>
                        <tbody>
                            @foreach($topPaths as $row)
                                <tr><td><code>/{{ $row['path'] }}</code></td><td><strong>{{ number_format($row['views']) }}</strong></td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>
        </div>

        <div style="flex: 1 1 340px;">
            <div class="panel">
                <h3>Top referrers</h3>
                @if($topReferrers->isEmpty())
                    <div class="empty-note">No referrers yet.</div>
                @else
                    <div class="table-responsive"><table>
                        <thead><tr><th>Host</th><th>Views</th></tr></thead>
                        <tbody>
                            @foreach($topReferrers as $row)
                                <tr><td>{{ e($row['referrer']) }}</td><td><strong>{{ number_format($row['views']) }}</strong></td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="panel">
                <h3>Devices</h3>
                @if($deviceBreakdown->isEmpty())
                    <div class="empty-note">No device data yet.</div>
                @else
                    <div class="table-responsive"><table>
                        <thead><tr><th>Device</th><th>Views</th></tr></thead>
                        <tbody>
                            @foreach($deviceBreakdown as $row)
                                <tr><td>{{ ucfirst($row['device']) }}</td><td><strong>{{ number_format($row['views']) }}</strong></td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>

            <div class="panel">
                <h3>Browsers</h3>
                @if($browserBreakdown->isEmpty())
                    <div class="empty-note">No browser data yet.</div>
                @else
                    <div class="table-responsive"><table>
                        <thead><tr><th>Browser</th><th>Views</th></tr></thead>
                        <tbody>
                            @foreach($browserBreakdown as $row)
                                <tr><td>{{ e($row['browser']) }}</td><td><strong>{{ number_format($row['views']) }}</strong></td></tr>
                            @endforeach
                        </tbody>
                    </table></div>
                @endif
            </div>
        </div>
    </div>
@endsection
