@extends('admin.layouts.app')

@section('title', 'Redirects')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-1">Redirects</h1>
    <p class="text-muted">Per-site 301/302 map. Every active redirect below rewrites the matching path for this site.</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5 mb-3">Add redirect</h2>
        <form method="POST" action="{{ route('admin.redirects.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label" for="from_path">From path</label>
                <input type="text" name="from_path" id="from_path" class="form-control" required
                       placeholder="/old-page" value="{{ old('from_path') }}">
                @error('from_path')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-5">
                <label class="form-label" for="to_url">To URL</label>
                <input type="text" name="to_url" id="to_url" class="form-control" required
                       placeholder="/new-page or https://example.com/page" value="{{ old('to_url') }}">
                @error('to_url')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-2">
                <label class="form-label" for="type">Type</label>
                <select name="type" id="type" class="form-select">
                    <option value="301" {{ old('type', '301') === '301' ? 'selected' : '' }}>301 — permanent</option>
                    <option value="302" {{ old('type') === '302' ? 'selected' : '' }}>302 — temporary</option>
                </select>
                @error('type')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Add redirect</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($redirects->isEmpty())
            <p class="text-muted mb-0">No redirects yet.</p>
        @else
            <div class="table-responsive"><table class="table table-sm">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Type</th>
                        <th>Hits</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($redirects as $redirect)
                        <tr>
                            <td><code>/{{ $redirect->from_path }}</code></td>
                            <td><a href="{{ $redirect->to_url }}" target="_blank" rel="noopener">{{ $redirect->to_url }}</a></td>
                            <td><span class="badge bg-secondary">{{ $redirect->type }}</span></td>
                            <td>{{ number_format($redirect->hit_count) }}</td>
                            <td>
                                @if($redirect->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.redirects.toggle', $redirect) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $redirect->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}" class="d-inline"
                                      onsubmit="return confirm('Delete this redirect?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </div>
</div>
@endsection
