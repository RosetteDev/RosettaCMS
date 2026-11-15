@extends('themes.rosetta.layout')

@section('content')
    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>About Rosetta CMS</h1>
            <p>Open source. Community driven. Future ready.</p>
        </div>
    </div>

    <!-- About Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div class="section-title">
                <h2>Our Story</h2>
            </div>
            
            <div style="max-width: 800px; margin: 0 auto; line-height: 1.8;">
                <p style="margin-bottom: 1.5rem;">
                    Rosetta CMS was born from a desire to bring the power of modern frameworks
                    to content management. We saw developers struggling with the limitations
                    of traditional CMS platforms and wanted to create something that combined
                    the best of both worlds.
                </p>
                <p style="margin-bottom: 1.5rem;">
                    Built on Laravel, Rosetta CMS provides the robust architecture and
                    performance that modern applications require, while maintaining the
                    ease of use and familiarity that content creators expect.
                </p>
                <p style="margin-bottom: 1.5rem;">
                    Our mission is to empower developers to build exceptional websites
                    and applications without compromising on flexibility, performance,
                    or security.
                </p>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section style="background-color: var(--light-color); padding: 4rem 0;">
        <div class="container">
            <div class="section-title">
                <h2>Our Mission</h2>
            </div>
            
            <div class="grid">
                <div class="card">
                    <h3>Open Source First</h3>
                    <p>We believe in the power of open source. All our code is freely available under the GNU GPLv3 license.</p>
                </div>
                
                <div class="card">
                    <h3>Developer Friendly</h3>
                    <p>Designed with developers in mind, providing clean APIs, comprehensive documentation, and easy extensibility.</p>
                </div>
                
                <div class="card">
                    <h3>Community Driven</h3>
                    <p>Our success depends on our community. We welcome contributions, feedback, and collaboration from everyone.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section style="padding: 4rem 0;">
        <div class="container">
            <div class="section-title">
                <h2>Get In Touch</h2>
            </div>
            
            <div style="max-width: 600px; margin: 0 auto;">
                <p style="text-align: center; margin-bottom: 2rem;">
                    Have questions? Want to contribute? Join our community!
                </p>
                
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                    <a href="https://github.com/RosettaDev/RosettaCMS" class="btn btn-primary">GitHub</a>
                    <a href="https://github.com/RosettaDev/RosettaCMS/discussions" class="btn btn-outline">Discussions</a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline">Contact Form</a>
                </div>
            </div>
        </div>
    </section>
@endsection
