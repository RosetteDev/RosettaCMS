@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>Contact Us</h1>
            <p>We'd love to hear from you</p>
        </div>
    </div>

    <!-- Contact Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 3rem;">
                <!-- Contact Form -->
                <div>
                    <h2 style="margin-bottom: 1.5rem; font-size: 1.75rem;">Send a Message</h2>
                    
                    <form class="contact-form" action="{{ url('/contact') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                    </form>
                </div>
                
                <!-- Contact Information -->
                <div>
                    <h2 style="margin-bottom: 1.5rem; font-size: 1.75rem;">Get In Touch</h2>
                    
                    <div style="background-color: var(--light-color); border-radius: 8px; padding: 2rem; margin-bottom: 2rem;">
                        <h3 style="margin-bottom: 1rem;">Our Information</h3>
                        
                        <div style="margin-bottom: 1rem;">
                            <strong style="display: block; color: var(--primary-color);">Email</strong>
                            <span>info@rosettacms.com</span>
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <strong style="display: block; color: var(--primary-color);">GitHub</strong>
                            <span><a href="https://github.com/RosettaDev/RosettaCMS" style="color: var(--primary-color);">github.com/RosettaDev/RosettaCMS</a></span>
                        </div>
                        
                        <div style="margin-bottom: 1rem;">
                            <strong style="display: block; color: var(--primary-color);">Discussions</strong>
                            <span><a href="https://github.com/RosettaDev/RosettaCMS/discussions" style="color: var(--primary-color);">GitHub Discussions</a></span>
                        </div>
                        
                        <div>
                            <strong style="display: block; color: var(--primary-color);">Report Issues</strong>
                            <span><a href="https://github.com/RosettaDev/RosettaCMS/issues" style="color: var(--primary-color);">GitHub Issues</a></span>
                        </div>
                    </div>
                    
                    <div>
                        <h3 style="margin-bottom: 1rem;">Connect With Us</h3>
                        
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <a href="https://github.com/RosettaDev" class="btn btn-outline" style="flex: 1;">GitHub</a>
                            <a href="https://twitter.com/RosettaCMS" class="btn btn-outline" style="flex: 1;">Twitter</a>
                            <a href="https://linkedin.com/company/rosettacms" class="btn btn-outline" style="flex: 1;">LinkedIn</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
