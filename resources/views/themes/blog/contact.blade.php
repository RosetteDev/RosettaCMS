@extends('themes.blog.layout')

@section('content')
    <section class="container" style="padding:3.5rem 0;max-width:700px;">
        <h1 style="font-size:2.25rem;margin-bottom:1rem;">Contact</h1>
        <p style="color:var(--secondary-color);margin-bottom:1.5rem;">Send a message and we will reply promptly.</p>
        @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
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
    </section>
@endsection
