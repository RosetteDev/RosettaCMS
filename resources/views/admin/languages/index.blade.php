@extends('admin.layouts.app')

@section('content')
    <div class="hero">
        <div class="container">
            <h1>Languages</h1>
            <p>Manage the languages available on your site. The default language is used when no other is selected.</p>
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

    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ route('admin.languages.create') }}" class="btn btn-primary">+ Add Language</a>
        </div>
    </div>

    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if($languages->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-language fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No languages yet. Add one to enable translations.</p>
            </div>
        @else
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Language</th>
                        <th>Code</th>
                        <th>RTL</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($languages as $language)
                        <tr>
                            <td>
                                <strong>{{ $language->flag }} {{ $language->name }}</strong>
                                @if($language->native_name && $language->native_name !== $language->name)
                                    <div class="small text-muted">{{ $language->native_name }}</div>
                                @endif
                            </td>
                            <td><code>{{ $language->code }}</code></td>
                            <td>
                                @if($language->isRtl())
                                    <span class="badge bg-info text-dark">RTL</span>
                                @else
                                    <span class="badge bg-secondary">LTR</span>
                                @endif
                            </td>
                            <td>
                                @if($language->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-warning text-dark">Disabled</span>
                                @endif
                            </td>
                            <td>
                                @if($language->is_default)
                                    <span class="badge bg-primary">Default</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-end" style="white-space: nowrap;">
                                <a href="{{ route('admin.languages.edit', $language) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.languages.toggle', $language) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $language->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                                @if(!$language->is_default)
                                    <form method="POST" action="{{ route('admin.languages.destroy', $language) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this language and all its translations?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
