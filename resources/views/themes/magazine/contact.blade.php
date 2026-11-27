@extends('themes.magazine.layout')

@section('content')
    <section class="container" style="padding:3.5rem 0;">
        <h1 style="font-size:2.5rem;margin-bottom:1rem;">Contact</h1>
        <p style="color:var(--secondary-color);margin-bottom:1.5rem;">Write to us — we read every message.</p>
        @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:2.5rem;">
            <div>
                <form action="{{ url('/contact') }}" method="POST">
                    @csrf
                    <div style="position:absolute;left:-9999px;" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required style="width:100%;padding:0.7rem;border:1px solid var(--border-color);background:#fff;border-radius:0.375rem;">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required style="width:100%;padding:0.7rem;border:1px solid var(--border-color);background:#fff;border-radius:0.375rem;">
                    </div>
                    <div style="margin-bottom:1rem;">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required style="width:100%;padding:0.7rem;border:1px solid var(--border-color);background:#fff;border-radius:0.375rem;">
                    </div>
                    <div style="margin-bottom:1.25rem;">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required rows="6" style="width:100%;padding:0.7rem;border:1px solid var(--border-color);background:#fff;border-radius:0.375rem;">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send message</button>
                </form>
            </div>
            <div>
                <div style="background:#fff;border:1px solid var(--border-color);border-radius:0.5rem;padding:1.5rem;">
                    @if($settings->admin_email ?? null)
                        <div style="margin-bottom:1rem;"><strong style="color:var(--accent-color);font-family:-apple-system,sans-serif;font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;">Email</strong><br><a href="mailto:{{ $settings->admin_email }}">{{ $settings->admin_email }}</a></div>
                    @endif
                    <div><strong style="color:var(--accent-color);font-family:-apple-system,sans-serif;font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;">Website</strong><br><a href="{{ url('/') }}">{{ url('/') }}</a></div>
                </div>
            </div>
        </div>
    </section>
@endsection
