@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Pages</h1>
            <p>Manage your static content (About, Contact, etc.)</p>
        </div>
    </div>

    <!-- Actions Bar -->
    <div style="padding: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ url('/admin/pages/create') }}" class="btn btn-primary">+ New Page</a>
        </div>
        <div>
            <form style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search pages..." style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; width: 200px;">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <!-- Pages Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($pages) > 0)
            <table style="width: 100%; border-collapse: collapse;">
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
                    @foreach($pages as $page)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <a href="{{ url('/admin/pages/' . $page->id . '/edit') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ Str::limit($page->title, 50) }}
                                </a>
                                <div style="font-size: 0.8rem; color: var(--text-color); margin-top: 0.25rem;">
                                    {{ Str::limit($page->slug, 30) }}
                                </div>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $page->user->name ?? 'Unknown' }}
                            </td>
                            <td style="padding: 1rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem;
                                    @if($page->status === 'published') background-color: var(--success-color); color: white;
                                    @elseif($page->status === 'draft') background-color: var(--warning-color); color: white;
                                    @else background-color: var(--secondary-color); color: white; @endif">
                                    {{ ucfirst($page->status) }}
                                </span>
                            </td>
                            <td style="padding: 1rem; color: var(--text-color);">
                                {{ $page->published_at ? $page->published_at->diffForHumans() : 'Not published' }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="{{ url('/admin/pages/' . $page->id . '/edit') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div style="padding: 1rem; display: flex; justify-content: center;">
                {{ $pages->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No pages found. <a href="{{ url('/admin/pages/create') }}" style="color: var(--primary-color);">Create one</a>!</p>
            </div>
        @endif
    </div>
@endsection
