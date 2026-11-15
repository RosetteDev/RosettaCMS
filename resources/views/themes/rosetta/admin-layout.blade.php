@extends('themes.rosetta.layout')

@section('content')
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>{{ $title ?? 'Admin Panel' }}</h2>
            <a href="{{ url('/admin/posts/create') }}" class="btn btn-primary">+ New Post</a>
        </div>

        @yield('admin-content')
    </div>
@endsection
