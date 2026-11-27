@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Event Calendar</h1>
            <p>{{ $month->format('F Y') }} — {{ $eventCount }} event{{ $eventCount === 1 ? '' : 's' }}</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; padding: 1rem 0;">
        <!-- Calendar card -->
        <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.5rem;">
            <!-- Month navigation -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; gap: 0.5rem;">
                <a href="{{ route('admin.event.calendar', ['month' => $prevMonth]) }}" class="btn btn-outline-secondary" style="padding: 0.4rem 0.9rem;">&larr; Prev</a>
                <h2 style="margin: 0; font-size: 1.2rem;">{{ $month->format('F Y') }}</h2>
                <a href="{{ route('admin.event.calendar', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary" style="padding: 0.4rem 0.9rem;">Next &rarr;</a>
            </div>

            <!-- Weekday header -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 4px;">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayName)
                    <div style="text-align: center; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); padding: 0.25rem 0; text-transform: uppercase;">{{ $dayName }}</div>
                @endforeach
            </div>

            <!-- Day cells -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
                @foreach($weeks as $week)
                    @foreach($week as $dayCell)
                        <div style="min-height: 76px; border-radius: 6px; border: 1px solid var(--border-color); padding: 4px;
                            background-color: {{ $dayCell['isToday'] ? 'var(--primary-color)' : ($dayCell['inMonth'] ? 'white' : 'var(--light-color)') }};
                            opacity: {{ $dayCell['inMonth'] ? '1' : '0.55' }};">
                            <div style="font-size: 0.75rem; font-weight: 600; text-align: right;
                                color: {{ $dayCell['isToday'] ? 'white' : 'var(--text-color)' }};">
                                {{ $dayCell['date']->format('j') }}
                            </div>
                            @foreach($dayCell['events']->take(2) as $event)
                                <a href="{{ route('admin.custom-posts.edit', ['event', $event->id]) }}"
                                   title="{{ $event->title }}"
                                   style="display: block; font-size: 0.65rem; line-height: 1.25; padding: 2px 4px; margin-top: 2px; border-radius: 4px; text-decoration: none;
                                      background-color: {{ $event->isScheduled() ? 'var(--warning-color)' : 'var(--success-color)' }};
                                      color: white; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ Str::limit($event->title, 24) }}
                                </a>
                            @endforeach
                            @if($dayCell['events']->count() > 2)
                                <div style="font-size: 0.65rem; color: var(--text-muted); padding: 0 2px;">+{{ $dayCell['events']->count() - 2 }} more</div>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>

            <div style="display: flex; gap: 1.25rem; margin-top: 1rem; font-size: 0.8rem; color: var(--text-muted); flex-wrap: wrap;">
                <span><span style="display: inline-block; width: 10px; height: 10px; border-radius: 3px; background-color: var(--success-color); vertical-align: middle; margin-right: 4px;"></span>Event</span>
                <span><span style="display: inline-block; width: 10px; height: 10px; border-radius: 3px; background-color: var(--warning-color); vertical-align: middle; margin-right: 4px;"></span>Scheduled</span>
            </div>
        </div>

        <!-- Upcoming events -->
        <div style="background-color: var(--light-color); border-radius: 8px; padding: 1.5rem;">
            <h2 style="font-size: 1.05rem; margin: 0 0 1rem;">Upcoming Events</h2>
            @if($upcoming->count() > 0)
                <ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($upcoming as $event)
                        <li style="background-color: white; border: 1px solid var(--border-color); border-radius: 8px; padding: 0.75rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                                <a href="{{ route('admin.custom-posts.edit', ['event', $event->id]) }}" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">{{ Str::limit($event->title, 50) }}</a>
                                <span style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap;">
                                    {{ $event->published_at->format('M j') }}
                                </span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">
                                {{ $event->isScheduled() ? 'Scheduled' : $event->status }} &middot; {{ $event->published_at->format('Y-m-d H:i') }}
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p style="color: var(--text-muted); margin: 0;">No upcoming events.</p>
            @endif

            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <a href="{{ route('admin.custom-posts.index', 'event') }}" class="btn btn-outline-secondary" style="padding: 0.5rem 1rem;">All Events</a>
                <a href="{{ route('admin.custom-posts.create', 'event') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; margin-left: 0.5rem;">+ New Event</a>
            </div>
        </div>
    </div>
@endsection
