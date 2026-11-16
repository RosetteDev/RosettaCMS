@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Dashboard</h1>
            <p>Welcome back! Here's what's happening with your content.</p>
        </div>
    </div>

    <!-- Dashboard Stats -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <div class="card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📝</div>
                    <h3 style="color: var(--primary-color);">{{ $stats['total_posts'] ?? 0 }}</h3>
                    <p>Total Posts</p>
                </div>
                
                <div class="card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📄</div>
                    <h3 style="color: var(--primary-color);">{{ $stats['total_pages'] ?? 0 }}</h3>
                    <p>Total Pages</p>
                </div>
                
                <div class="card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🖼️</div>
                    <h3 style="color: var(--primary-color);">{{ $stats['total_media'] ?? 0 }}</h3>
                    <p>Media Files</p>
                </div>
                
                <div class="card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🌐</div>
                    <h3 style="color: var(--primary-color);">{{ $stats['total_sites'] ?? 0 }}</h3>
                    <p>Sites</p>
                </div>
                
                <div class="card" style="text-align: center; padding: 2rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👥</div>
                    <h3 style="color: var(--primary-color);">{{ $stats['total_users'] ?? 0 }}</h3>
                    <p>Users</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Posts -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <div class="section-title">
                <h2>Recent Posts</h2>
            </div>
            
            @if(count($recent_posts) > 0)
                <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
                    @foreach($recent_posts as $post)
                        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                    {{ $post->title }}
                                </a>
                                <p style="font-size: 0.9rem; color: var(--text-color); margin-top: 0.25rem;">
                                    {{ ucfirst($post->status) }} • {{ $post->user->name ?? 'Author' }} • {{ $post->updated_at->diffForHumans() }}
                                </p>
                            </div>
                            
                            <div>
                                <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" class="btn btn-primary" style="padding: 0.5rem 1rem;">Edit</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="text-align: center; color: var(--text-color);">No posts yet. <a href="{{ url('/admin/posts/create') }}" style="color: var(--primary-color);">Create one</a>!</p>
            @endif
        </div>
    </section>

    <!-- Quick Actions -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <div class="section-title">
                <h2>Quick Actions</h2>
            </div>
            
            <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <a href="{{ url('/admin/posts/create') }}" class="card" style="text-decoration: none; transition: all 0.2s;">
                    <h3 style="margin-bottom: 1rem;">📝 New Post</h3>
                    <p>Create a new blog post or article</p>
                </a>
                
                <a href="{{ url('/admin/pages/create') }}" class="card" style="text-decoration: none; transition: all 0.2s;">
                    <h3 style="margin-bottom: 1rem;">📄 New Page</h3>
                    <p>Create a new static page</p>
                </a>
                
                <a href="{{ url('/admin/media/create') }}" class="card" style="text-decoration: none; transition: all 0.2s;">
                    <h3 style="margin-bottom: 1rem;">🖼️ Upload Media</h3>
                    <p>Upload images, videos, and other files</p>
                </a>
                
                <a href="{{ url('/admin/sites') }}" class="card" style="text-decoration: none; transition: all 0.2s;">
                    <h3 style="margin-bottom: 1rem;">🌐 Manage Sites</h3>
                    <p>Add or configure multiple sites</p>
                </a>
                
                <a href="{{ url('/admin/settings') }}" class="card" style="text-decoration: none; transition: all 0.2s;">
                    <h3 style="margin-bottom: 1rem;">⚙️ Settings</h3>
                    <p>Configure your CMS preferences</p>
                </a>
            </div>
        </div>
    </section>
@endsection
