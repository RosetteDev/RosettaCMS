@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Tags</h1>
            <p>Tag your posts for easy filtering</p>
        </div>
    </div>

    <!-- Actions Bar -->
    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ url('/admin/tags/create') }}" class="btn btn-primary">+ New Tag</a>
        </div>
    </div>

    <!-- Tags Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($tags) > 0)
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
                    @foreach($tags as $tag)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <a href="{{ url('/admin/tags/' . $tag->slug . '/edit') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ $tag->name }}
                                </a>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color); font-family: monospace;">
                                {{ $tag->slug }}
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ Str::limit($tag->description, 50) }}
                            </td>
                            <td style="padding: 1rem;">
                                @if($tag->color)
                                    <span style="display: inline-block; width: 1rem; height: 1rem; border-radius: 4px; background-color: {{ $tag->color }}; border: 1px solid #ccc;" title="{{ $tag->color }}"></span>
                                    <span style="font-family: monospace; font-size: 0.8rem;">{{ $tag->color }}</span>
                                @else
                                    <span style="color: #9ca3af;">No color</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $tag->posts_count }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="{{ url('/admin/tags/' . $tag->slug . '/edit') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No tags found. <a href="{{ url('/admin/tags/create') }}" style="color: var(--primary-color);">Create one</a>!</p>
            </div>
        @endif
    </div>
@endsection
