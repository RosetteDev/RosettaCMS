@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Pending Comments</h1>
            <p>Comments awaiting moderation</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted);">Total Pending</h3>
            <p style="margin: 0; font-size: 2rem; font-weight: bold; color: #f59e0b;">{{ $statistics['pending'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.5rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted);">Total Comments</h3>
            <p style="margin: 0; font-size: 2rem; font-weight: bold; color: var(--primary-color);">{{ $statistics['total'] }}</p>
        </div>
    </div>

    <!-- Instructions -->
    <div style="background-color: #dbeafe; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <p style="margin: 0; color: #1e40af;">
            <strong>Note:</strong> Comments from registered users auto-publish. Comments from guests require manual approval.
        </p>
    </div>

    <!-- Comments Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($comments) > 0)
            <div class="table-responsive"><table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: var(--dark-color); color: white;">
                    <tr>
                        <th style="padding: 1rem; text-align: left;">Author</th>
                        <th style="padding: 1rem; text-align: left;">Comment</th>
                        <th style="padding: 1rem; text-align: left;">Post</th>
                        <th style="padding: 1rem; text-align: left;">Author Type</th>
                        <th style="padding: 1rem; text-align: left;">Date</th>
                        <th style="padding: 1rem; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comments as $comment)
                        <tr style="border-bottom: 1px solid var(--border-color); background-color: #fffbeb;">
                            <td style="padding: 1rem;">
                                <strong>{{ $comment->author_name ?? 'Guest' }}</strong><br>
                                <span style="color: var(--text-muted); font-size: 0.9rem;">
                                    {{ $comment->author_email ?? 'N/A' }}
                                    @if($comment->user)
                                        <br><span style="color: #10b981;">✓ Registered User</span>
                                    @endif
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
                                @if($comment->user)
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem; background-color: #d1fae5; color: #065f46;">Registered</span>
                                @else
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem; background-color: #fef3c7; color: #92400e;">Guest</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-muted);">
                                {{ $comment->created_at->diffForHumans() }}
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <a href="{{ url('/admin/comments/' . $comment->id . '/edit') }}" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">View</a>
                                <form action="{{ url('/admin/comments/' . $comment->id . '/approve') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;" title="Approve">✓ Approve</button>
                                </form>
                                <form action="{{ url('/admin/comments/' . $comment->id . '/reject') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;" onclick="return confirm('Reject this comment?')" title="Reject">✗ Reject</button>
                                </form>
                                <form action="{{ url('/admin/comments/' . $comment->id . '/spam') }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;" onclick="return confirm('Mark as spam?')" title="Mark as Spam">⚠ Spam</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-color);">
                <p>No pending comments. <a href="{{ url('/admin/comments') }}" style="color: var(--primary-color);">View all comments</a>.</p>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    <div style="padding: 1rem;">
        {{ $comments->links() }}
    </div>
@endsection
