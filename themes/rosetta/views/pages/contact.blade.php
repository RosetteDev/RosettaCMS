@extends('themes.rosetta.views.layouts.app')

@section('content')
<!-- Hero Section -->
<div class="hero" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); min-height: 300px; display: flex; align-items: center;">
    <div class="container">
        <h1>Contact Us</h1>
        <p style="font-size: 1.2rem;">Get in touch with our team</p>
    </div>
</div>

<!-- Contact Content -->
<section style="padding: 4rem 0;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 3rem;">
                <h2 style="font-size: 2.5rem; margin-bottom: 1rem; color: var(--text-color);">We'd Love to Hear From You</h2>
                <p style="color: var(--secondary-color);">Fill out the form below and we'll get back to you as soon as possible.</p>
            </div>
            
            <form method="POST" action="{{ url('/contact/submit') }}" style="background: var(--bg-color); padding: 2rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
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
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
            </form>
            
            <div style="margin-top: 3rem;">
                <h3 style="font-size: 1.75rem; margin-bottom: 1.5rem; color: var(--text-color);">Contact Information</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                    <div class="card" style="padding: 1.5rem; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.75rem;">📍</div>
                        <h4 style="margin-bottom: 0.5rem;">Address</h4>
                        <p style="color: var(--secondary-color);">123 CMS Street<br>Web City, WC 12345</p>
                    </div>
                    
                    <div class="card" style="padding: 1.5rem; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.75rem;">📧</div>
                        <h4 style="margin-bottom: 0.5rem;">Email</h4>
                        <p style="color: var(--secondary-color);">info@example.com<br>support@example.com</p>
                    </div>
                    
                    <div class="card" style="padding: 1.5rem; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 0.75rem;">📞</div>
                        <h4 style="margin-bottom: 0.5rem;">Phone</h4>
                        <p style="color: var(--secondary-color);">(123) 456-7890<br>(123) 456-7891</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection