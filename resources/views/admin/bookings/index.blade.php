@extends('admin.layouts.app')

@section('content')
    <!-- Admin Header -->
    <div class="hero">
        <div class="container">
            <h1>Bookings</h1>
            <p>Appointment and booking requests from the public booking form</p>
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
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Pending</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: #f59e0b;">{{ $statistics['pending'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Confirmed</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: #10b981;">{{ $statistics['confirmed'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Completed</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: #6366f1;">{{ $statistics['completed'] }}</p>
        </div>
        <div style="background-color: var(--light-color); padding: 1.25rem; border-radius: 8px;">
            <h3 style="margin: 0 0 0.5rem 0; color: var(--text-muted); font-size: 0.9rem;">Cancelled</h3>
            <p style="margin: 0; font-size: 1.75rem; font-weight: bold; color: #ef4444;">{{ $statistics['cancelled'] }}</p>
        </div>
    </div>

    <!-- Filter and Search -->
    <div style="background-color: var(--light-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
        <form method="GET" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <input type="text" name="search" placeholder="Search bookings..." value="{{ $search }}" style="flex: 1; min-width: 200px; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; background: transparent; color: var(--text-color);">
            <select name="status" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 4px; background: transparent; color: var(--text-color);">
                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <!-- Bookings Table -->
    <div style="background-color: var(--light-color); border-radius: 8px; overflow: hidden;">
        @if(count($bookings) > 0)
            <div class="table-responsive">
                <table style="width: 100%; border-collapse: collapse; min-width: 900px;">
                    <thead style="background-color: var(--dark-color); color: white;">
                        <tr>
                            <th style="padding: 1rem; text-align: left;">Name</th>
                            <th style="padding: 1rem; text-align: left;">Service</th>
                            <th style="padding: 1rem; text-align: left;">Date / Time</th>
                            <th style="padding: 1rem; text-align: left;">Status</th>
                            <th style="padding: 1rem; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr style="border-bottom: 1px solid var(--border-color); @if($booking->status === 'pending') background-color: #fffbeb; @endif">
                                <td style="padding: 1rem;">
                                    <strong>{{ $booking->name }}</strong><br>
                                    <a href="mailto:{{ $booking->email }}" style="color: var(--primary-color); font-size: 0.85rem;">{{ $booking->email }}</a>
                                    @if($booking->phone)
                                        <div style="color: var(--text-muted); font-size: 0.85rem;">{{ $booking->phone }}</div>
                                    @endif
                                    @if($booking->notes)
                                        <div style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem; max-width: 260px; overflow: hidden; text-overflow: ellipsis;" title="{{ $booking->notes }}">{{ Str::limit($booking->notes, 60) }}</div>
                                    @endif
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: var(--text-color);">
                                    {{ $booking->service ?: '(not specified)' }}
                                </td>
                                <td style="padding: 1rem; white-space: nowrap; font-size: 0.9rem; color: var(--text-color);">
                                    {{ $booking->date->format('M j, Y') }}
                                    @if($booking->time)
                                        <div style="color: var(--text-muted); font-size: 0.85rem;">{{ $booking->time }}</div>
                                    @endif
                                </td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem;
                                        @if($booking->status === 'pending') background-color: #fef3c7; color: #92400e;
                                        @elseif($booking->status === 'confirmed') background-color: #d1fae5; color: #065f46;
                                        @elseif($booking->status === 'completed') background-color: #e0e7ff; color: #3730a3;
                                        @else background-color: #fee2e2; color: #991b1b; @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <form method="POST" action="{{ route('admin.bookings.status', $booking->id) }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="confirmed">
                                        <button type="submit" class="btn btn-primary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; margin: 0.1rem;" @if($booking->status === 'confirmed') disabled @endif>Confirm</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.bookings.status', $booking->id) }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-outline-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; margin: 0.1rem;" @if($booking->status === 'completed') disabled @endif>Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.bookings.status', $booking->id) }}" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-outline-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; margin: 0.1rem; color: #991b1b;" @if($booking->status === 'cancelled') disabled @endif>Cancel</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.bookings.destroy', $booking->id) }}" style="display: inline;" onsubmit="return confirm('Delete this booking?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-secondary" style="padding: 0.25rem 0.6rem; font-size: 0.75rem; color: #991b1b;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding: 1rem; display: flex; justify-content: center;">
                {{ $bookings->links() }}
            </div>
        @else
            <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                <i class="fas fa-calendar-check" style="font-size: 2.5rem; margin-bottom: 1rem; display: block;"></i>
                No bookings found.
            </div>
        @endif
    </div>
@endsection
