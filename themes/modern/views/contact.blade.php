@extends('themes.modern::layouts.app')

@section('content')
<!-- Hero Section -->
<div class="hero">
    <div class="container">
        <h1>{{ $page->title ?? 'Contact Us' }}</h1>
        <p>We'd love to hear from you</p>
    </div>
</div>

<!-- Contact Section -->
<section style="padding: 4rem 0;">
    <div class="container">
        @if(session('status'))
            <div style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:8px;padding:0.9rem 1.1rem;margin-bottom:2rem;">
                {{ session('status') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:8px;padding:0.9rem 1.1rem;margin-bottom:2rem;">
                {{ session('error') }}
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

        <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));">
            <!-- Contact Form -->
            <div>
                <h2 style="margin-bottom: 1.5rem; font-size: 1.75rem;">Send a Message</h2>

                <form class="contact-form" action="{{ url('/contact') }}" method="POST">
                    @csrf
                    <div style="position:absolute;left:-9999px;" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="email" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="subject" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" value="{{ old('subject') }}" required>
                    </div>

                    <div style="margin-bottom: 1.5rem;">
                        <label for="message" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Message</label>
                        <textarea id="message" name="message" rows="6" class="form-control" required>{{ old('message') }}</textarea>
                    </div>

                    @foreach(($customFields ?? []) as $field)
                        <div style="margin-bottom: 1.5rem;">
                            <label for="{{ $field['name'] }}" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">{{ $field['label'] }}@if($field['required']) <span style="color:#dc2626;">*</span>@endif</label>
                            @if($field['type'] === 'textarea')
                                <textarea id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="3" class="form-control" @if($field['required']) required @endif placeholder="{{ $field['placeholder'] }}">{{ old($field['name']) }}</textarea>
                            @elseif($field['type'] === 'number')
                                <input type="number" id="{{ $field['name'] }}" name="{{ $field['name'] }}" class="form-control" value="{{ old($field['name']) }}" @if($field['required']) required @endif placeholder="{{ $field['placeholder'] }}">
                            @else
                                <input type="{{ $field['type'] === 'email' ? 'email' : 'text' }}" id="{{ $field['name'] }}" name="{{ $field['name'] }}" class="form-control" value="{{ old($field['name']) }}" @if($field['required']) required @endif placeholder="{{ $field['placeholder'] }}">
                            @endif
                        </div>
                    @endforeach

                    <button type="submit" class="btn" style="width: 100%;">Send Message</button>
                </form>
            </div>

            <!-- Contact Information -->
            <div>
                <h2 style="margin-bottom: 1.5rem; font-size: 1.75rem;">Get In Touch</h2>

                <div class="card" style="margin-bottom: 2rem;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Our Information</h3>

                    <div style="margin-bottom: 1rem;">
                        <strong style="display: block; color: var(--primary-color);">Email</strong>
                        <span>{{ $settings->admin_email ?? 'info@rosettacms.com' }}</span>
                    </div>

                    @if($settings->address || $settings->phone)
                    <div style="margin-bottom: 1rem;">
                        <strong style="display: block; color: var(--primary-color);">Address</strong>
                        <span>{{ $settings->address }}</span>
                    </div>
                    @endif

                    @if($settings->phone)
                    <div>
                        <strong style="display: block; color: var(--primary-color);">Phone</strong>
                        <span>{{ $settings->phone }}</span>
                    </div>
                    @endif
                </div>

                <div class="card">
                    <h3 style="margin-bottom: 1rem; font-size: 1.25rem;">Follow Us</h3>
                    <p style="color: var(--secondary-color);">Stay connected with us across social media.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
