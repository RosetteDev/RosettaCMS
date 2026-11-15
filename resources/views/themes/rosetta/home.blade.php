@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>Welcome to Rosetta CMS</h1>
            <p>A modern, flexible content management system built with Laravel</p>
            <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                <a href="{{ url('/admin') }}" class="btn btn-primary">Get Started</a>
                <a href="https://github.com/RosettaDev/RosettaCMS" class="btn btn-outline">View on GitHub</a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section>
        <div class="container">
            <div class="section-title">
                <h2>Powerful Features</h2>
                <p>Everything you need to build and manage your website</p>
            </div>
            
            <div class="grid">
                <div class="card">
                    <div class="feature-icon">🎨</div>
                    <h3>Visual Editor</h3>
                    <p>Hybrid block-based and inline visual editor for easy content creation</p>
                </div>
                
                <div class="card">
                    <div class="feature-icon">🌍</div>
                    <h3>Multi-Site</h3>
                    <p>Manage multiple websites from a single installation</p>
                </div>
                
                <div class="card">
                    <div class="feature-icon">🔍</div>
                    <h3>SEO Tools</h3>
                    <p>Built-in SEO optimization features for better search rankings</p>
                </div>
                
                <div class="card">
                    <div class="feature-icon">🔌</div>
                    <h3>Plugin System</h3>
                    <p>Extensible plugin architecture with WordPress compatibility</p>
                </div>
                
                <div class="card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure</h3>
                    <p>Enterprise-grade security with role-based access control</p>
                </div>
                
                <div class="card">
                    <div class="feature-icon">⚡</div>
                    <h3>Fast Performance</h3>
                    <p>Optimized for speed with caching and efficient database queries</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div class="section-title">
                <h2>About Rosetta CMS</h2>
                <p>Open source. Community driven. Future ready.</p>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                <p style="font-size: 1.1rem; margin-bottom: 2rem;">
                    Rosetta CMS is a modern content management system built on Laravel, 
                    designed for developers who want the power of a full framework with 
                    the ease of use of a traditional CMS.
                </p>
                <p style="font-size: 1.1rem; margin-bottom: 2rem;">
                    With WordPress plugin compatibility, you can use familiar plugins 
                    while taking advantage of Laravel's robust architecture and performance.
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section style="background: linear-gradient(135deg, var(--primary-color), #2563eb); color: white; padding: 4rem 0; text-align: center;">
        <div class="container">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Get Started?</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem;">Join thousands of developers building with Rosetta CMS</p>
            <a href="https://github.com/RosettaDev/RosettaCMS" class="btn btn-secondary" style="background-color: white; color: var(--primary-color);">Star on GitHub</a>
            <a href="https://github.com/RosettaDev/RosettaCMS/issues" class="btn btn-outline" style="background-color: transparent; border-color: white; color: white; margin-left: 1rem;">Report an Issue</a>
        </div>
    </section>
@endsection
