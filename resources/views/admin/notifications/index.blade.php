@extends('admin.layouts.app')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Notifications</h1>
            <p class="text-muted mb-0">Pending moderation, scheduled posts and recent activity</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h2 class="h6 mb-0">Summary</h2></div>
                <div class="list-group list-group-flush">
                    <a href="{{ url('/admin/comments/pending') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Pending comments</span>
                        <span class="badge bg-warning text-dark">{{ $counts['pending_comments'] }}</span>
                    </a>
                    <a href="{{ url('/admin/posts?status=pending') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Pending posts</span>
                        <span class="badge bg-warning text-dark">{{ $counts['pending_posts'] }}</span>
                    </a>
                    <a href="{{ url('/admin/posts') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <span>Scheduled (7 days)</span>
                        <span class="badge bg-info text-dark">{{ $counts['scheduled_posts'] }}</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent"><h2 class="h6 mb-0">Pending comments</h2></div>
                <div class="card-body p-0">
                    @forelse($pendingComments as $comment)
                        <div class="list-group list-group-flush">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold">{{ $comment->author_name ?? 'Anonymous' }}</div>
                                        <div class="small text-muted">{{ $comment->post?->title ?? 'Unknown post' }}</div>
                                        <div class="small">{{ Str::limit($comment->content, 120) }}</div>
                                    </div>
                                    <a href="{{ url('/admin/comments/pending') }}" class="btn btn-sm btn-outline-primary align-self-start flex-shrink-0">Review</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3 mb-0">No pending comments.</p>
                    @endforelse
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent"><h2 class="h6 mb-0">Pending + scheduled posts</h2></div>
                <div class="card-body p-0">
                    @forelse($pendingPosts as $post)
                        <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>{{ $post->title }}</span>
                            <span class="badge bg-warning text-dark">Pending</span>
                        </a>
                    @empty
                        @if($pendingPosts->isEmpty())
                            <p class="text-muted text-center py-3 mb-0">No pending posts.</p>
                        @endif
                    @endforelse
                    @foreach($scheduledPosts as $post)
                        <a href="{{ url('/admin/posts/' . $post->id . '/edit') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>{{ $post->title }}</span>
                            <span class="badge bg-info text-dark">{{ $post->published_at->format('M d H:i') }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h2 class="h6 mb-0">Recent activity</h2></div>
                <div class="card-body p-0">
                    @forelse($recentActivity as $entry)
                        <div class="list-group list-group-flush">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between gap-3">
                                    <div class="small">
                                        <span class="fw-semibold">{{ $entry->action ?? 'activity' }}</span>
                                        @if($entry->description ?? null)
                                            <span class="text-muted">— {{ $entry->description }}</span>
                                        @endif
                                    </div>
                                    <small class="text-muted flex-shrink-0">{{ $entry->created_at?->diffForHumans() }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3 mb-0">No recent activity.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
