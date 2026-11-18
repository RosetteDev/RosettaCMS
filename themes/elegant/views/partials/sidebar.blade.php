<div class="sidebar">
    <!-- Search Widget -->
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Search</h5>
        <form action="{{ url('/search') }}" method="GET" class="search-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..." required>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Categories Widget -->
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Categories</h5>
        <ul class="category-list list-group">
            @foreach($categories as $category)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="{{ route('category.show', $category->slug) }}" class="text-decoration-none">
                        {{ $category->name }}
                    </a>
                    <span class="badge bg-light text-dark border">
                        {{ $category->posts_count ?? 0 }}
                    </span>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Recent Posts Widget -->
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Recent Posts</h5>
        <ul class="recent-posts-list list-group">
            @foreach($recentPosts as $post)
                <li class="list-group-item">
                    <a href="{{ route('post.show', $post->slug) }}" class="text-decoration-none">
                        {{ $post->title }}
                    </a>
                    <small class="text-muted">{{ $post->created_at->format('M j, Y') }}</small>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Tags Cloud Widget -->
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Tags</h5>
        <div class="tag-cloud">
            @foreach($tags as $tag)
                <a href="{{ route('tag.show', $tag->slug) }}" class="tag-cloud-item">
                    {{ $tag->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Social Media Widget -->
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Follow Us</h5>
        <div class="social-links">
            <a href="#" class="social-link">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="social-link">
                <i class="fab fa-twitter"></i>
            </a>
            <a href="#" class="social-link">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="social-link">
                <i class="fab fa-linkedin-in"></i>
            </a>
        </div>
    </div>

    <!-- Newsletter Widget -->
    <div class="sidebar-widget mb-4">
        <h5 class="sidebar-widget-title">Newsletter</h5>
        <p class="newsletter-text">Subscribe to our newsletter for the latest updates.</p>
        <form action="#" method="POST" class="newsletter-form">
            <div class="mb-2">
                <input type="email" class="form-control" placeholder="Your email" required>
            </div>
            <button type="submit" class="btn btn-outline-primary w-100">
                Subscribe
            </button>
        </form>
    </div>
</div>
