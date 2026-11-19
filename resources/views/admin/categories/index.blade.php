@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Categories</h1>
            <p>Organize your posts by category</p>
        </div>
    </div>

    <!-- Actions Bar -->
    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ url('/admin/categories/create') }}" class="btn btn-primary">+ New Category</a>
        </div>
    </div>

    <!-- Categories Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($categories) > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-color); color: white;">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">Name</th>
                        <th style="padding: 1rem; text-align: left;">Slug</th>
                        <th style="padding: 1rem; text-align: left;">Description</th>
                        <th style="padding: 1rem; text-align: left;">Color</th>
                        <th style="padding: 1rem; text-align: left;">Posts</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <a href="{{ url('/admin/categories/' . $category->slug . '/edit') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ $category->name }}
                                </a>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color); font-family: monospace;">
                                {{ $category->slug }}
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ Str::limit($category->description, 50) }}
                            </td>
                            <td style="padding: 1rem;">
                                @if($category->color)
                                    <span style="display: inline-block; width: 1rem; height: 1rem; border-radius: 4px; background-color: {{ $category->color }}; border: 1px solid #ccc;" title="{{ $category->color }}"></span>
                                    <span style="font-family: monospace; font-size: 0.8rem;">{{ $category->color }}</span>
                                @else
                                    <span style="color: #9ca3af;">No color</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $category->posts_count }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="{{ url('/admin/categories/' . $category->slug . '/edit') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No categories found. <a href="{{ url('/admin/categories/create') }}" style="color: var(--primary-color);">Create one</a>!</p>
            </div>
        @endif
    </div>
@endsection
