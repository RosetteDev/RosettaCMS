@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Chat Conversations</h1>
            <p>In-app support messages from website visitors</p>
        </div>
    </div>

    @if(session('status'))
        <div style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">{{ session('status') }}</div>
    @endif

    <!-- Summary -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Conversations</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: var(--primary-color);">{{ $conversations->count() }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Unread Messages</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: #f59e0b;">{{ $unreadTotal }}</p>
        </div>
    </div>

    <!-- Conversations List -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if($conversations->count() > 0)
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse; min-width: 720px;">
                    <thead style="background-color: var(--dark-color); color: white;">
                        <tr>
                            <th style="padding: 1rem; text-align: left;">Visitor</th>
                            <th style="padding: 1rem; text-align: left;">Last Message</th>
                            <th style="padding: 1rem; text-align: left;">Messages</th>
                            <th style="padding: 1rem; text-align: left;">Last Activity</th>
                            <th style="padding: 1rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conversations as $conversation)
                            <tr style="border-bottom: 1px solid var(--border-color); @if($conversation['unread'] > 0) background-color: #fffbeb; @endif">
                                <td style="padding: 1rem; font-family: monospace; font-size: 0.85rem; color: var(--text-color);">
                                    {{ Str::limit($conversation['session_id'], 18, '') }}
                                    @if($conversation['unread'] > 0)
                                        <span style="display: inline-block; margin-left: 0.5rem; padding: 0.1rem 0.5rem; border-radius: 999px; background-color: #f59e0b; color: white; font-size: 0.7rem; font-family: inherit;">{{ $conversation['unread'] }} unread</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem; max-width: 320px; color: var(--text-color);">
                                    @if($conversation['last_message'])
                                        <span style="display: inline-block; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; margin-right: 0.4rem; background-color: {{ $conversation['last_message']->sender === 'admin' ? '#e0e7ff' : '#e5e7eb' }}; color: {{ $conversation['last_message']->sender === 'admin' ? '#3730a3' : '#374151' }};">{{ $conversation['last_message']->sender === 'admin' ? 'You' : 'Visitor' }}</span>
                                        {{ Str::limit($conversation['last_message']->message, 90) }}
                                    @endif
                                </td>
                                <td style="padding: 1rem; color: var(--text-muted);">{{ $conversation['total'] }}</td>
                                <td style="padding: 1rem; color: var(--text-muted); white-space: nowrap;">{{ $conversation['last_at'] ? \Carbon\Carbon::parse($conversation['last_at'])->diffForHumans() : '' }}</td>
                                <td style="padding: 1rem; text-align: right;">
                                    <a href="{{ route('admin.chat.show', $conversation['session_id']) }}" class="btn btn-primary" style="padding: 0.35rem 0.9rem; font-size: 0.8rem;">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                <i class="fas fa-comments" style="font-size: 2.5rem; margin-bottom: 1rem; display: block;"></i>
                No chat conversations yet.
            </div>
        @endif
    </div>
@endsection
