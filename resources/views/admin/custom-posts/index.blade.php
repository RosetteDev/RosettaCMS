@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>{{ $def['plural'] }}</h1>
            <p>{{ $def['description'] }}</p>
        </div>
    </div>

    <!-- Actions Bar -->
    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <a href="{{ route('admin.custom-posts.create', $type) }}" class="btn btn-primary">+ New {{ $def['label'] }}</a>
            @if($type === 'event')
                <a href="{{ route('admin.event.calendar') }}" class="btn btn-outline-secondary" style="padding: 0.5rem 1rem;">View Calendar</a>
            @endif
        </div>
        <form style="display: flex; gap: 0.5rem;" method="GET" action="{{ route('admin.custom-posts.index', $type) }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search {{ strtolower($def['plural']) }}..." style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; width: 220px;">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($posts) > 0)
            <div class="table-responsive"><table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-color); color: white;">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">Title</th>
                        <th style="padding: 1rem; text-align: left;">Author</th>
                        <th style="padding: 1rem; text-align: left;">Status</th>
                        <th style="padding: 1rem; text-align: left;">Published</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <a href="{{ route('admin.custom-posts.edit', [$type, $post->id]) }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ Str::limit($post->title, 60) }}
                                </a>
                                <div style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">
                                    {{ Str::limit($post->slug, 40) }}
                                </div>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $post->user->name ?? 'Unknown' }}
                            </td>
                            <td style="padding: 1rem;">
                                @if($post->isScheduled())
                                    <span class="status-badge" style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; background-color: var(--secondary-color); color: white;">Scheduled</span>
                                @else
                                    <span class="status-badge" style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem;
                                        @if($post->status === 'published') background-color: var(--success-color); color: white;
                                        @elseif($post->status === 'draft') background-color: var(--warning-color); color: white;
                                        @elseif($post->status === 'trash') background-color: #dc3545; color: white;
                                        @else background-color: var(--secondary-color); color: white; @endif">
                                        {{ ucfirst($post->status) }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $post->published_at ? $post->published_at->diffForHumans() : 'Not published' }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="{{ route('admin.custom-posts.edit', [$type, $post->id]) }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit</a>
                                <form method="POST" action="{{ route('admin.custom-posts.destroy', [$type, $post->id]) }}" style="display: inline;" onsubmit="return confirm('Delete this {{ strtolower($def['label']) }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" style="padding: 0.5rem 1rem;" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>

            <!-- Pagination -->
            <div style="padding: 1rem; display: flex; justify-content: center;">
                {{ $posts->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No {{ strtolower($def['plural']) }} found. <a href="{{ route('admin.custom-posts.create', $type) }}" style="color: var(--primary-color);">Create one</a>!</p>
            </div>
        @endif
    </div>
@endsection
