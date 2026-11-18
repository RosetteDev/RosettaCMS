@extends('themes.modern.views.layouts.app')

@section('content')
<div class="content">
    <div class="container">
        <h1 style="font-size: 2.5rem; margin-bottom: 2rem; color: var(--primary-color);">{{ $page->title ?? 'Contact' }}</h1>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="{{ url('/contact/submit') }}">
                @csrf
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Name</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="subject" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Subject</label>
                    <input type="text" id="subject" name="subject" class="form-control" required>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Message</label>
                    <textarea id="message" name="message" rows="6" class="form-control" required></textarea>
                </div>
                
                <button type="submit" class="btn" style="width: 100%;">Send Message</button>
            </form>
        </div>
    </div>
</div>
@endsection