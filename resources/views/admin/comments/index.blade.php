@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Comments</h1>
            <p>Manage comments on your posts</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted);">Total</h3>
            <p style="margin: 0; font-size: 2rem; font-weight: bold; color: var(--primary-color);">{{ $statistics['total'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted);">Approved</h3>
            <p style="margin: 0; font-size: 2rem; font-weight: bold; color: #10b981;">{{ $statistics['approved'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted);">Pending</h3>
            <p style="margin: 0; font-size: 2rem; font-weight: bold; color: #f59e0b;">{{ $statistics['pending'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted);">Spam</h3>
            <p style="margin: 0; font-size: 2rem; font-weight: bold; color: #ef4444;">{{ $statistics['spam'] }}</p>
        </div>
    </div>

    <!-- Filter and Search -->
    <div style="background-color: var(--light-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        <form method="GET" style="display: flex; gap: 0.5rem; flex: 1; min-width: 200px;">
            <input type="text" name="search" placeholder="Search comments..." value="{{ $search }}" style="flex: 1; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
            <select name="status" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px;">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="spam" {{ $status === 'spam' ? 'selected' : '' }}>Spam</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- Bulk Actions -->
    <form id="bulk-form" method="POST" style="margin-bottom: 1rem;">
        @csrf
        <div style="background-color: var(--light-color); padding: 1rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" name="action" value="approve" class="btn btn-success" onclick="return confirm('Approve selected comments?')">✓ Approve</button>
                <button type="submit" name="action" value="reject" class="btn btn-warning" onclick="return confirm('Reject selected comments?')">✗ Reject</button>
                <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Delete selected comments?')">🗑 Delete</button>
            </div>
            <span style="color: var(--text-muted);">Selected: <span id="selected-count">0</span></span>
        </div>

        <!-- Comments Table -->
        <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
            @if(count($comments) > 0)
                <div class="table-responsive"><table style="width: 100%; border-collapse: collapse;">
                    <thead style="background-color: var(--dark-color); color: white;">
                        <tr>
                            <th style="padding: 1rem; text-align: left; width: 40px;">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th style="padding: 1rem; text-align: left;">Author</th>
                            <th style="padding: 1rem; text-align: left;">Comment</th>
                            <th style="padding: 1rem; text-align: left;">Post</th>
                            <th style="padding: 1rem; text-align: left;">Status</th>
                            <th style="padding: 1rem; text-align: left;">Date</th>
                            <th style="padding: 1rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comments as $comment)
                            <tr style="border-bottom: 1px solid var(--border-color); @if($comment->status === 'pending') background-color: #fffbeb; @elseif($comment->status === 'spam') background-color: #fef2f2; @endif">
                                <td style="padding: 1rem;">
                                    <input type="checkbox" name="ids[]" value="{{ $comment->id }}" class="comment-checkbox">
                                </td>
                                <td style="padding: 1rem;">
                                    <strong>{{ $comment->author_name ?? 'Guest' }}</strong><br>
                                    <span style="color: var(--text-muted); font-size: 0.9rem;">
                                        {{ $comment->author_email ?? 'N/A' }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; max-width: 300px;">
                                    <div style="max-height: 3em; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $comment->content }}
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    <a href="{{ url('/admin/posts/' . $comment->post_id . '/edit') }}" style="color: var(--primary-color);">
                                        {{ $comment->post->title ?? 'Unknown Post' }}
                                    </a>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem;
                                        @if($comment->status === 'approved') background-color: #d1fae5; color: #065f46;
                                        @elseif($comment->status === 'pending') background-color: #fef3c7; color: #92400e;
                                        @elseif($comment->status === 'rejected') background-color: #fee2e2; color: #991b1b;
                                        @else background-color: #fee2e2; color: #991b1b; @endif">
                                        {{ ucfirst($comment->status) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-muted);">
                                    {{ $comment->created_at->diffForHumans() }}
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <a href="{{ url('/admin/comments/' . $comment->id . '/edit') }}" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">View</a>
                                    @if($comment->status === 'pending')
                                        <form action="{{ url('/admin/comments/' . $comment->id . '/approve') }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;" title="Approve">✓</button>
                                        </form>
                                        <form action="{{ url('/admin/comments/' . $comment->id . '/reject') }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-warning" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;" title="Reject">✗</button>
                                        </form>
                                        <form action="{{ url('/admin/comments/' . $comment->id . '/spam') }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;" title="Mark as Spam">⚠</button>
                                        </form>
                                    @endif
                                    <form action="{{ url('/admin/comments/' . $comment->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;" onclick="return confirm('Delete this comment?')" title="Delete">🗑</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            @else
                <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                    <p>No comments found.</p>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        <div style="padding: 1rem;">
            {{ $comments->links() }}
        </div>
    </form>
@endsection

@section('scripts')
<script>
    // Select all checkbox
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.comment-checkbox');
        checkboxes.forEach checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Update selected count when checkboxes change
    document.querySelectorAll('.comment-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    function updateSelectedCount() {
        const count = document.querySelectorAll('.comment-checkbox:checked').length;
        document.getElementById('selected-count').textContent = count;
    }
</script>
@endsection
