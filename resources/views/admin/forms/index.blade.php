@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Form Submissions</h1>
            <p>Contact form entries and custom form data</p>
        </div>
    </div>

    @if(session('status'))
        <div style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">{{ session('status') }}</div>
    @endif

    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Total</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: var(--primary-color);">{{ $statistics['total'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">New</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: #10b981;">{{ $statistics['new'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Spam</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: #ef4444;">{{ $statistics['spam'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Handled</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: #6366f1;">{{ $statistics['handled'] }}</p>
        </div>
    </div>

    <!-- Filter and Search -->
    <div style="background-color: var(--light-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        <form method="GET" style="display: flex; gap: 0.5rem; flex: 1; min-width: 200px;">
            <input type="text" name="search" placeholder="Search submissions..." value="{{ $search }}" style="flex: 1; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; background: transparent; color: var(--text-color);">
            <select name="status" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; background: transparent; color: var(--text-color);">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="new" {{ $status === 'new' ? 'selected' : '' }}>New</option>
                <option value="read" {{ $status === 'read' ? 'selected' : '' }}>Read</option>
                <option value="spam" {{ $status === 'spam' ? 'selected' : '' }}>Spam</option>
                <option value="handled" {{ $status === 'handled' ? 'selected' : '' }}>Handled</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- Submissions Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($submissions) > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 720px;">
                    <thead style="background-color: var(--dark-color); color: white;">
                        <tr>
                            <th style="padding: 1rem; text-align: left;">Name</th>
                            <th style="padding: 1rem; text-align: left;">Subject</th>
                            <th style="padding: 1rem; text-align: left;">Status</th>
                            <th style="padding: 1rem; text-align: left;">Spam Score</th>
                            <th style="padding: 1rem; text-align: left;">Date</th>
                            <th style="padding: 1rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $submission)
                            <tr style="border-bottom: 1px solid var(--border-color); @if($submission->status === 'spam') background-color: #fef2f2; @elseif($submission->status === 'new') background-color: #ecfdf5; @endif">
                                <td style="padding: 1rem;">
                                    <strong>{{ $submission->name }}</strong><br>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">{{ $submission->email }}</span>
                                </td>
                                <td style="padding: 1rem; max-width: 260px;">
                                    <div style="max-height: 2.6em; overflow: hidden; text-overflow: ellipsis;">{{ $submission->subject ?: '(no subject)' }}</div>
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem;
                                        @if($submission->status === 'new') background-color: #d1fae5; color: #065f46;
                                        @elseif($submission->status === 'read') background-color: #e0e7ff; color: #3730a3;
                                        @elseif($submission->status === 'spam') background-color: #fee2e2; color: #991b1b;
                                        @else background-color: #f3e8ff; color: #6b21a8; @endif">
                                        {{ ucfirst($submission->status) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-muted);">
                                    {{ $submission->spam_score }}/100
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-muted); white-space: nowrap;">
                                    {{ $submission->created_at->diffForHumans() }}
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <a href="{{ url('/admin/forms/' . $submission->id) }}" class="btn btn-primary" style="padding: 0.25rem 0.75rem; font-size: 0.8rem;">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 1rem; display: flex; justify-content: center;">
                {{ $submissions->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                <i class="fas fa-inbox" style="font-size: 2.5rem; margin-bottom: 1rem; display: block;"></i>
                No submissions found.
            </div>
        @endif
    </div>
@endsection
