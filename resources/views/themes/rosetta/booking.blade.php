@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>Book an Appointment</h1>
            <p>Request a booking — we'll confirm your appointment</p>
        </div>
    </div>

    <!-- Booking Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            @if(session('status'))
                <div style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:8px;padding:0.9rem 1.1rem;margin-bottom:2rem;">
                    {{ session('status') }}
                </div>
            @endif
            @if($errors->any())
                <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:0.9rem 1.1rem;margin-bottom:2rem;">
                    <strong>Please check the form:</strong>
                    <ul style="margin:0.5rem 0 0 1.25rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/book') }}" method="POST" style="max-width: 640px; margin: 0 auto;">
                @csrf
                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone <span style="font-weight: 400; color: var(--text-muted, #6b7280);">(optional)</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                </div>

                <div class="form-group">
                    <label for="service">Service <span style="font-weight: 400; color: var(--text-muted, #6b7280);">(optional)</span></label>
                    <input type="text" id="service" name="service" value="{{ old('service') }}" placeholder="e.g. Consultation, Meeting">
                </div>

                <div class="form-group" style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" value="{{ old('date') }}" min="{{ now()->toDateString() }}" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="time">Time <span style="font-weight: 400; color: var(--text-muted, #6b7280);">(optional)</span></label>
                        <input type="time" id="time" name="time" value="{{ old('time') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes <span style="font-weight: 400; color: var(--text-muted, #6b7280);">(optional)</span></label>
                    <textarea id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn" style="width: 100%; padding: 0.9rem; font-size: 1.05rem;">Request Booking</button>
            </form>
        </div>
    </section>
@endsection
