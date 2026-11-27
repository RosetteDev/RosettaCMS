@extends('themes.rosetta.layout')

@section('content')
    <!-- Events Hero -->
    <div class="hero">
        <div class="container">
            <h1>Events</h1>
            <p>Upcoming and past events from the site.</p>
        </div>
    </div>

    <!-- Upcoming events -->
    <section style="padding: 2rem 0;">
        <div class="container">
            <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Upcoming</h2>
            @if($upcoming->count() > 0)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                    @foreach($upcoming as $event)
                        <div class="card" style="background-color: var(--light-color); border-radius: 8px; padding: 1.5rem; border-left: 4px solid var(--primary-color);">
                            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <h3 style="margin: 0; font-size: 1.15rem;">{{ $event->title }}</h3>
                                @if($event->published_at)
                                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--primary-color); white-space: nowrap;">
                                        {{ $event->published_at->format('M j, Y') }}
                                    </span>
                                @endif
                            </div>
                            @if($event->excerpt)
                                <p style="color: var(--text-color); margin-bottom: 0.75rem;">{{ Str::limit($event->plain_excerpt, 200) }}</p>
                            @endif
                            <div style="font-size: 0.8rem; color: var(--text-muted, #6b7280);">
                                {{ $event->status === 'pending' ? 'Scheduled' : 'Published' }}
                                @if($event->published_at) &middot; {{ $event->published_at->format('Y-m-d H:i') }} @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: var(--text-muted, #6b7280);">No upcoming events right now.</p>
            @endif
        </div>
    </section>

    <!-- Past events -->
    @if($past->count() > 0)
        <section style="padding: 1rem 0 3rem;">
            <div class="container">
                <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem;">Past Events</h2>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($past as $event)
                        <li style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; background-color: var(--light-color); border-radius: 8px; padding: 0.75rem 1rem;">
                            <span style="font-weight: 500;">{{ $event->title }}</span>
                            <span style="font-size: 0.85rem; color: var(--text-muted, #6b7280); white-space: nowrap;">
                                {{ $event->published_at->format('M j, Y') }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif
@endsection
