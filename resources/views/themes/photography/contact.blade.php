@extends('themes.photography.layout')

@section('content')
    <section class="section" style="padding-top:4rem;">
        <div class="container">
            <div class="section-title"><h1>Contact</h1><p>Let's work together</p></div>
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
                            <label for="name" style="font-family:-apple-system,sans-serif;font-size:0.8rem;letter-spacing:0.06em;text-transform:uppercase;color:#94a3b8;">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required style="width:100%;padding:0.75rem;border:1px solid #1e293b;background:#0f172a;color:#f1f5f9;border-radius:0.5rem;">
                        </div>
                        <div style="margin-bottom:1rem;">
                            <label for="email" style="font-family:-apple-system,sans-serif;font-size:0.8rem;letter-spacing:0.06em;text-transform:uppercase;color:#94a3b8;">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required style="width:100%;padding:0.75rem;border:1px solid #1e293b;background:#0f172a;color:#f1f5f9;border-radius:0.5rem;">
                        </div>
                        <div style="margin-bottom:1rem;">
                            <label for="subject" style="font-family:-apple-system,sans-serif;font-size:0.8rem;letter-spacing:0.06em;text-transform:uppercase;color:#94a3b8;">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required style="width:100%;padding:0.75rem;border:1px solid #1e293b;background:#0f172a;color:#f1f5f9;border-radius:0.5rem;">
                        </div>
                        <div style="margin-bottom:1rem;">
                            <label for="message" style="font-family:-apple-system,sans-serif;font-size:0.8rem;letter-spacing:0.06em;text-transform:uppercase;color:#94a3b8;">Message</label>
                            <textarea id="message" name="message" required rows="6" style="width:100%;padding:0.75rem;border:1px solid #1e293b;background:#0f172a;color:#f1f5f9;border-radius:0.5rem;">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Send message</button>
                    </form>
                </div>
                <div>
                    <div style="background:#1e293b;border-radius:0.75rem;padding:1.75rem;">
                        @if($settings->admin_email ?? null)
                            <div style="margin-bottom:1.25rem;"><strong style="color:#f59e0b;font-family:-apple-system,sans-serif;font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;">Email</strong><br><a href="mailto:{{ $settings->admin_email }}" style="font-family:-apple-system,sans-serif;">{{ $settings->admin_email }}</a></div>
                        @endif
                        <div><strong style="color:#f59e0b;font-family:-apple-system,sans-serif;font-size:0.75rem;letter-spacing:0.1em;text-transform:uppercase;">Website</strong><br><a href="{{ url('/') }}" style="font-family:-apple-system,sans-serif;">{{ url('/') }}</a></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
