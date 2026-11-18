@extends('themes.rosetta.views.layouts.app')

@section('content')
<!-- Hero Section -->
<div class="hero" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); min-height: 300px; display: flex; align-items: center;">
    <div class="container">
        <h1>About Us</h1>
        <p style="font-size: 1.2rem;">Learn more about who we are and what we do</p>
    </div>
</div>

<!-- About Content -->
<section style="padding: 4rem 0;">
    <div class="container">
        <div style="max-width: 900px; margin: 0 auto; line-height: 1.8;">
            <h2 style="font-size: 2.5rem; margin-bottom: 2rem; color: var(--text-color);">Who We Are</h2>
            
            <p style="margin-bottom: 1.5rem;">{{ $page->content ?? 'Welcome to our about page. This content can be managed through the admin panel.' }}</p>
            
            <h3 style="font-size: 1.75rem; margin: 2.5rem 0 1.5rem; color: var(--text-color);">Our Mission</h3>
            <p style="margin-bottom: 1.5rem;">We are dedicated to providing the best content management solution for developers and businesses alike. Our mission is to make web content management accessible, flexible, and powerful.</p>
            
            <h3 style="font-size: 1.75rem; margin: 2.5rem 0 1.5rem; color: var(--text-color);">What We Offer</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <div class="card" style="padding: 2rem;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">🎨</div>
                    <h4 style="margin-bottom: 0.75rem; color: var(--primary-color);">Visual Editor</h4>
                    <p style="color: var(--secondary-color);">Easy-to-use visual editor for non-technical users</p>
                </div>
                <div class="card" style="padding: 2rem;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">🚀</div>
                    <h4 style="margin-bottom: 0.75rem; color: var(--primary-color);">Fast Performance</h4>
                    <p style="color: var(--secondary-color);">Optimized for speed and efficiency</p>
                </div>
                <div class="card" style="padding: 2rem;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">🔒</div>
                    <h4 style="margin-bottom: 0.75rem; color: var(--primary-color);">Secure</h4>
                    <p style="color: var(--secondary-color);">Enterprise-grade security features</p>
                </div>
            </div>
            
            <h3 style="font-size: 1.75rem; margin: 2.5rem 0 1.5rem; color: var(--text-color);">Our Team</h3>
            <p style="margin-bottom: 1.5rem;">We are a passionate team of developers, designers, and content creators who believe in the power of open-source software.</p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section style="background-color: var(--bg-color); padding: 4rem 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center;">
            <div>
                <div style="font-size: 3rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">10K+</div>
                <div style="color: var(--secondary-color);">Downloads</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">500+</div>
                <div style="color: var(--secondary-color);">Active Sites</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">24/7</div>
                <div style="color: var(--secondary-color);">Support</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: bold; color: var(--primary-color); margin-bottom: 0.5rem;">100%</div>
                <div style="color: var(--secondary-color);">Open Source</div>
            </div>
        </div>
    </div>
</section>
@endsection