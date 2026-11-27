@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Custom Widgets</h1>
            <p>Manage custom widgets for your dashboard, sidebar and frontend footer</p>
        </div>
    </div>

    <!-- Actions Bar -->
    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ route('admin.widgets.create') }}" class="btn btn-primary">+ Add Widget</a>
        </div>
    </div>

    <!-- Widgets List -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($widgets) > 0)
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-color); color: white;">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">Name</th>
                        <th style="padding: 1rem; text-align: left;">Slug</th>
                        <th style="padding: 1rem; text-align: left;">Type</th>
                        <th style="padding: 1rem; text-align: left;">Location</th>
                        <th style="padding: 1rem; text-align: center;">Status</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($widgets as $widget)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <a href="{{ route('admin.widgets.edit', $widget->id) }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ $widget->name }}
                                </a>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $widget->slug }}
                            </td>
                            <td style="padding: 1rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: var(--secondary-color); color: white;">
                                    {{ ucfirst($widget->type) }}
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: #17a2b8; color: white;">
                                    {{ ucfirst($widget->location) }}
                                </span>
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                @if($widget->enabled)
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: var(--success-color); color: white;">
                                        Enabled
                                    </span>
                                @else
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: #6c757d; color: white;">
                                        Disabled
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="{{ route('admin.widgets.edit', $widget->id) }}" class="btn btn-primary" style="padding: 0.5rem 1rem; margin: 0.25rem;">Edit</a>
                                <a href="{{ route('admin.widgets.toggle') }}" 
                                   class="btn btn-outline-secondary" 
                                   style="padding: 0.5rem 1rem; margin: 0.25rem;"
                                   onclick="event.preventDefault(); if(confirm('Toggle widget status?')) { document.getElementById('toggle-form-{{ $widget->id }}').submit(); }">
                                    @if($widget->enabled) Disable @else Enable @endif
                                </a>
                                <form id="toggle-form-{{ $widget->id }}" action="{{ route('admin.widgets.toggle') }}" method="POST" style="display: none;">
                                    @csrf
                                    <input type="hidden" name="widget_id" value="{{ $widget->id }}">
                                </form>
                                <a href="{{ route('admin.widgets.destroy', $widget->id) }}" 
                                   class="btn btn-danger" 
                                   style="padding: 0.5rem 1rem; margin: 0.25rem;"
                                   onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this widget?')) { document.getElementById('delete-form-{{ $widget->id }}').submit(); }">
                                    Delete
                                </a>
                                <form id="delete-form-{{ $widget->id }}" action="{{ route('admin.widgets.destroy', $widget->id) }}" method="POST" style="display: none;">
                                    @method('DELETE')
                                    @csrf
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div style="padding: 1rem; display: flex; justify-content: center;">
                {{ $widgets->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No widgets found. <a href="{{ route('admin.widgets.create') }}" style="color: var(--primary-color);">Create one</a>!</p>
            </div>
        @endif
    </div>
@endsection