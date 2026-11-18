@extends('admin.layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Menus</h1>
            <p class="text-muted mb-0">Build navigation menus for your site's header and footer.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h2 class="h5 mb-0">Your menus</h2>
                </div>
                <div class="card-body p-0">
                    @if($menus->isEmpty())
                        <p class="text-muted p-4 mb-0">No menus yet. Create your first menu to start building navigation.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Items</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($menus as $menu)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.menus.edit', $menu) }}" class="fw-semibold text-decoration-none">
                                                    {{ $menu->name }}
                                                </a>
                                                <div class="text-muted small">{{ $menu->slug }}</div>
                                            </td>
                                            <td><span class="badge bg-light text-dark text-capitalize">{{ $menu->location }}</span></td>
                                            <td>{{ $menu->items_count }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Delete this menu and all of its items?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h2 class="h5 mb-0">Create a menu</h2>
                </div>
                <div class="card-body">
                    @if($site)
                        <form method="POST" action="{{ route('admin.menus.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="menu-name" class="form-label">Menu name</label>
                                <input type="text" name="name" id="menu-name" class="form-control"
                                       value="{{ old('name') }}" placeholder="Primary Navigation" required>
                            </div>
                            <div class="mb-3">
                                <label for="menu-location" class="form-label">Location</label>
                                <select name="location" id="menu-location" class="form-select">
                                    <option value="header" {{ old('location') === 'header' ? 'selected' : '' }}>Header</option>
                                    <option value="footer" {{ old('location') === 'footer' ? 'selected' : '' }}>Footer</option>
                                    <option value="custom" {{ old('location') === 'custom' ? 'selected' : '' }}>Custom</option>
                                </select>
                                <div class="form-text">The header menu appears automatically in your theme navigation.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create menu</button>
                        </form>
                    @else
                        <p class="text-muted mb-0">A site must exist before menus can be created.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
