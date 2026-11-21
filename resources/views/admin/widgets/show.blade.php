@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Widget Details</h1>
            <p>{{ $widget->name }}</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem;">
                    <div style="margin-bottom: 2rem;">
                        <h3>Widget Information</h3>
                        <table style="width: 100%; margin-top: 1rem;">
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Name:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); color: var(--text-color);">{{ $widget->name }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Slug:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); color: var(--text-color);">{{ $widget->slug }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Type:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: var(--secondary-color); color: white;">
                                        {{ ucfirst($widget->type) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Location:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: #17a2b8; color: white;">
                                        {{ ucfirst($widget->location) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Context:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: #6f42c1; color: white;">
                                        {{ ucfirst($widget->context) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Priority:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); color: var(--text-color);">{{ $widget->priority }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Status:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);">
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
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Created:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); color: var(--text-color);">
                                    {{ $widget->created_at->format('M d, Y H:i:s') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color);"><strong>Updated:</strong></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid var(--border-color); color: var(--text-color);">
                                    {{ $widget->updated_at->format('M d, Y H:i:s') }}
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-bottom: 2rem;">
                        <h3>Content Preview</h3>
                        <div style="padding: 1.5rem; background-color: #f8f9fa; border: 1px solid var(--border-color); border-radius: 6px;">
                            @if($widget->type === 'html')
                                {!! $widget->content !!}
                            @elseif($widget->type === 'rss')
                                <div class="rss-widget">
                                    <p class="text-muted">RSS feed from: {{ $widget->content }}</p>
                                    <p class="text-muted small">RSS content would be parsed and displayed here</p>
                                </div>
                            @else
                                <div class="custom-widget">
                                    <p class="text-muted">Custom widget content:</p>
                                    <pre style="padding: 1rem; background-color: white; border: 1px solid var(--border-color); border-radius: 4px; overflow-x: auto;">{{ $widget->content }}</pre>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <a href="{{ route('admin.widgets.edit', $widget->id) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ route('admin.widgets.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection