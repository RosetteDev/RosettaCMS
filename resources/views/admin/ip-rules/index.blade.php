@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>IP Rules</h1>
            <p>Restrict which networks can reach this site. Blocked IPs are always refused; allow-list rules only apply when at least one allow rule exists. Login and health-check routes always stay reachable.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(!$site)
        <div class="container mt-3">
            <div class="alert alert-warning">No site is resolved for this request.</div>
        </div>
    @else
        <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.25rem; margin-top: 1rem;">
            <h2 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 0.75rem;">Add a rule</h2>
            <form method="POST" action="{{ route('admin.ip-rules.store') }}" style="display: grid; grid-template-columns: 1fr 1.4fr 1.4fr auto; gap: 0.5rem; align-items: end;">
                @csrf
                <div>
                    <label for="type" style="display:block; font-size: 0.8rem; font-weight: 500; margin-bottom: 0.25rem;">Type</label>
                    <select id="type" name="type" class="form-control form-control-sm" required>
                        <option value="block" {{ old('type', 'block') === 'block' ? 'selected' : '' }}>Block</option>
                        <option value="allow" {{ old('type') === 'allow' ? 'selected' : '' }}>Allow</option>
                    </select>
                </div>
                <div>
                    <label for="cidr" style="display:block; font-size: 0.8rem; font-weight: 500; margin-bottom: 0.25rem;">CIDR / IP</label>
                    <input type="text" id="cidr" name="cidr" class="form-control form-control-sm" placeholder="203.0.113.0/24" value="{{ old('cidr') }}" required>
                    @error('cidr')
                        <p style="color: var(--error-color); font-size: 0.8rem; margin: 0.25rem 0 0;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="label" style="display:block; font-size: 0.8rem; font-weight: 500; margin-bottom: 0.25rem;">Label (optional)</label>
                    <input type="text" id="label" name="label" class="form-control form-control-sm" value="{{ old('label') }}" maxlength="255">
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.5rem 1rem;">Add</button>
            </form>
        </div>

        <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden; margin-top: 1.25rem;">
            @if($rules->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-shield-alt fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">No IP rules yet. Add one above to restrict this site.</p>
                </div>
            @else
                <div class="table-responsive"><table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>CIDR / IP</th>
                            <th>Label</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rules as $rule)
                            <tr>
                                <td>
                                    @if($rule->type === 'allow')
                                        <span class="badge bg-success">Allow</span>
                                    @else
                                        <span class="badge bg-danger">Block</span>
                                    @endif
                                </td>
                                <td><code>{{ $rule->cidr }}</code></td>
                                <td>{{ $rule->label ?: '—' }}</td>
                                <td>{{ $rule->created_at?->diffForHumans() }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.ip-rules.destroy', $rule) }}" style="display:inline" onsubmit="return confirm('Delete this rule?');">
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
    @endif
@endsection
