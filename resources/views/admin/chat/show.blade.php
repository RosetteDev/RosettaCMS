@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Conversation</h1>
            <p style="font-family: monospace;">{{ $session }}</p>
        </div>
    </div>

    @if(session('status'))
        <div style="background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.75rem 1rem; margin-bottom: 1rem;">{{ session('status') }}</div>
    @endif

    <div style="margin-bottom: 1rem;">
        <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary">← Back to conversations</a>
    </div>

    <!-- Thread -->
    <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.5rem; min-height: 240px; margin-bottom: 1rem;">
        @foreach($messages as $message)
            <div style="display: flex; margin-bottom: 1rem; justify-content: {{ $message->sender === 'admin' ? 'flex-end' : 'flex-start' }};">
                <div style="max-width: 75%; padding: 0.75rem 1rem; border-radius: 12px;
                    @if($message->sender === 'admin')
                        background-color: var(--primary-color); color: white; border-bottom-right-radius: 4px;
                    @else
                        background-color: white; color: var(--text-color); border: 1px solid var(--border-color); border-bottom-left-radius: 4px;
                    @endif">
                    <div style="font-size: 0.7rem; opacity: 0.75; margin-bottom: 0.35rem;">{{ $message->sender === 'admin' ? 'You' : 'Visitor' }} · {{ $message->created_at->format('M j, Y H:i') }}</div>
                    <div style="white-space: pre-wrap; word-break: break-word;">{{ $message->message }}</div>
                </div>
            </div>
        @endforeach
        @if($messages->isEmpty())
            <div style="text-align: center; color: var(--text-muted); padding: 2rem;">No messages yet.</div>
        @endif
    </div>

    <!-- Reply Form -->
    <form method="POST" action="{{ route('admin.chat.reply', $session) }}" style="background-color: var(--light-color); border-radius: 8px; padding: 1.25rem;">
        @csrf
        <label for="message" style="display: block; margin-bottom: 0.5rem; color: var(--text-color); font-weight: 600;">Reply</label>
        <textarea id="message" name="message" rows="4" required maxlength="2000" placeholder="Write a reply…" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 6px; background: transparent; color: var(--text-color); resize: vertical; box-sizing: border-box;">{{ old('message') }}</textarea>
        <div style="margin-top: 0.75rem; display: flex; justify-content: flex-end;">
            <button type="submit" class="btn btn-primary">Send Reply</button>
        </div>
    </form>
@endsection
