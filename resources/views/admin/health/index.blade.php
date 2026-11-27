@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Site Health</h1>
            <p>A snapshot of the site's operational readiness.</p>
        </div>
    </div>

    <div style="padding: 1rem 0;">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
            @if($allOk)
                <span class="badge bg-success" style="font-size: 1rem; padding: 0.5rem 1rem;"><i class="fas fa-check-circle me-1"></i> All checks passing</span>
            @else
                <span class="badge bg-warning text-dark" style="font-size: 1rem; padding: 0.5rem 1rem;"><i class="fas fa-exclamation-triangle me-1"></i> Some checks need attention</span>
            @endif
        </div>

        <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Check</th>
                        <th>Status</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checks as $check)
                        <tr>
                            <td style="font-weight: 600;">{{ $check['label'] }}</td>
                            <td>
                                @if($check['ok'])
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Pass</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Fail</span>
                                @endif
                            </td>
                            <td><small>{{ $check['detail'] }}</small></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>
    </div>
@endsection
