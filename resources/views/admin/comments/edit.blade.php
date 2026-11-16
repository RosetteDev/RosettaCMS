@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Comment Details</h1>
            <p>Manage comment on "{{ $comment->post->title ?? 'Unknown Post' }}"</p>
        </div>
    </div>

    <!-- Comment Info -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Comment Content -->
        <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <div>
                    <h2 style="margin: 0 0 0.5rem 0;">
                        {{ $comment->author_name ?? 'Guest' }}
                    </h2>
                    <p style="margin: 0; color: var(--text-muted);">
                        {{ $comment->author_email ?? 'No email' }} | 
                        IP: {{ $comment->ip_address ?? 'Unknown' }} |
                        {{ $comment->created_at->format('F j, Y g:i A') }}
                    </p>
                </div>
                <div>
                    <span style="padding: 0.5rem 1rem; border-radius: 4px; font-size: 0.9rem; font-weight: bold;
                        @if($comment->status === 'approved') background-color: #d1fae5; color: #065f46;
                        @elseif($comment->status === 'pending') background-color: #fef3c7; color: #92400e;
                        @elseif($comment->status === 'rejected') background-color: #fee2e2; color: #991b1b;
                        @else background-color: #fee2e2; color: #991b1b; @endif">
                        {{ ucfirst($comment->status) }}
                    </span>
                </div>
            </div>

            <div style="background-color: #f9fafb; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--primary-color); margin-bottom: 1.5rem;">
                <p style="margin: 0; white-space: pre-wrap;">{{ $comment->content }}</p>
            </div>

            <!-- Comment Details -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <strong>Author Type:</strong>
                    @if($comment->user)
                        <p style="color: #10b981;">Registered User ({{ $comment->user->name }})</p>
                    @else
                        <p style="color: #92400e;">Guest Comment</p>
                    @endif
                </div>
                <div>
                    <strong>Replies:</strong>
                    <p>{{ $comment->replies_count ?? 0 }}</p>
                </div>
                <div>
                    <strong>User Agent:</strong>
                    <p style="font-size: 0.8rem; word-break: break-all; color: var(--text-muted);">{{ $comment->user_agent ?? 'N/A' }}</p>
                </div>
                <div>
                    <strong>Parent Comment:</strong>
                    <p>{{ $comment->parent_id ? 'Yes' : 'No' }}</p>
                </div>
            </div>

            <!-- Status Actions -->
            <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                @if($comment->status === 'pending')
                    <form action="{{ url('/admin/comments/' . $comment->id . '/approve') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">✓ Approve</button>
                    </form>
                    <form action="{{ url('/admin/comments/' . $comment->id . '/reject') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Reject this comment?')">✗ Reject</button>
                    </form>
                    <form action="{{ url('/admin/comments/' . $comment->id . '/spam') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Mark as spam?')">⚠ Mark as Spam</button>
                    </form>
                @elseif($comment->status === 'approved')
                    <form action="{{ url('/admin/comments/' . $comment->id . '/reject') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Reject this comment?')">✗ Reject</button>
                    </form>
                @elseif($comment->status === 'rejected')
                    <form action="{{ url('/admin/comments/' . $comment->id . '/approve') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">✓ Approve</button>
                    </form>
                @endif
                <form action="{{ url('/admin/comments/' . $comment->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this comment permanently?')">🗑 Delete</button>
                </form>
            </div>

            <!-- Admin Reply Form -->
            <div>
                <h3 style="margin: 0 0 1rem 0;">Reply as Administrator</h3>
                <form action="{{ url('/admin/comments/' . $comment->id . '/reply') }}" method="POST">
                    @csrf
                    <textarea name="content" required placeholder="Write your reply here..." style="width: 100%; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; min-height: 100px; font-family: inherit;"></textarea>
                    <div style="margin-top: 0.5rem; text-align: right;">
                        <button type="submit" class="btn btn-primary">✓ Post Reply</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Post Info -->
            <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem;">
                <h3 style="margin: 0 0 1rem 0;">Post Information</h3>
                <p style="margin-bottom: 0.5rem;"><strong>Title:</strong></p>
                <p style="margin: 0 0 1rem 0;">{{ $comment->post->title }}</p>
                <a href="{{ url('/admin/posts/' . $comment->post_id . '/edit') }}" class="btn btn-primary" style="width: 100%;">Edit Post</a>
            </div>

            <!-- Author Info -->
            <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem;">
                <h3 style="margin: 0 0 1rem 0;">Author Information</h3>
                <p style="margin-bottom: 0.5rem;"><strong>Name:</strong></p>
                <p style="margin: 0 0 1rem 0;">{{ $comment->author_name ?? 'N/A' }}</p>
                <p style="margin-bottom: 0.5rem;"><strong>Email:</strong></p>
                <p style="margin: 0 0 1rem 0;">{{ $comment->author_email ?? 'N/A' }}</p>
                <p style="margin-bottom: 0.5rem;"><strong>Website:</strong></p>
                <p style="margin: 0;">
                    @if($comment->author_url)
                        <a href="{{ $comment->author_url }}" target="_blank" style="color: var(--primary-color);">{{ $comment->author_url }}</a>
                    @else
                        N/A
                    @endif
                </p>
            </div>

            <!-- Related Comments -->
            <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px;">
                <h3 style="margin: 0 0 1rem 0;">Replies</h3>
                @if($comment->replies->count() > 0)
                    <div style="margin-bottom: 1rem;">
                        @foreach($comment->replies as $reply)
                            <div style="padding: 0.75rem; background-color: #f3f4f6; border-radius: 8px; margin-bottom: 0.5rem;">
                                <p style="margin: 0 0 0.5rem 0;"><strong>{{ $reply->user->name ?? 'Admin' }}</strong></p>
                                <p style="margin: 0; font-size: 0.9rem; color: var(--text-color);">{{ $reply->content }}</p>
                                <p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; color: var(--text-muted);">{{ $reply->created_at->diffForHumans() }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="color: var(--text-muted);">No replies yet.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
