<div class="sidebar">
    <!-- Search Widget -->
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Search</h5>
        <form action="{{ url('/search') }}" method="GET">
            <input type="text" name="q" class="form-control" placeholder="Search..." required>
            <button type="submit" class="btn" style="width: 100%; margin-top: 0.75rem;">Search</button>
        </form>
    </div>

    <!-- Categories Widget -->
    @if($categories->isNotEmpty())
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Categories</h5>
        <ul class="category-list" style="list-style: none; padding: 0; margin: 0;">
            @foreach($categories as $category)
                <li class="list-group-item" style="display: flex; justify-content: space-between; align-items: center; gap: 1rem;">
                    <a href="{{ route('category.show', $category->slug) }}">{{ $category->name }}</a>
                    <span style="font-size: 0.85rem; color: var(--secondary-color); background: #f3f4f6; border-radius: 999px; padding: 0.15rem 0.6rem;">{{ $category->posts_count }}</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Recent Posts Widget -->
    @if($recentPosts->isNotEmpty())
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Recent Posts</h5>
        <ul class="recent-posts-list" style="list-style: none; padding: 0; margin: 0;">
            @foreach($recentPosts as $post)
                <li class="list-group-item">
                    <a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a>
                    <br>
                    <small style="color: var(--secondary-color);">{{ $post->published_at?->format('M j, Y') ?? $post->created_at->format('M j, Y') }}</small>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Tags Cloud Widget -->
    @if($tags->isNotEmpty())
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Tags</h5>
        <div class="tag-cloud">
            @foreach($tags as $tag)
                <a href="{{ route('tag.show', $tag->slug) }}" class="tag-cloud-item">{{ $tag->name }}</a>
            @endforeach
        </div>
    </div>
    @endif
</div>
