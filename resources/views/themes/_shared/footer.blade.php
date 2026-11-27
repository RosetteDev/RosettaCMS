@php
    $siteName = $settings->resolved_site_title ?? ($site->name ?? 'Rosetta CMS');
    $footerMenu = $footerMenu ?? null;
    $footerLinks = $footerMenu ? $footerMenu->rootItems : collect();
    $adminEmail = $settings->admin_email ?? null;
@endphp

<div class="container footer-container">
    <div class="footer-section">
        <h3>{{ $siteName }}</h3>
        <p>{{ $settings->resolved_site_description ?? ($site->description ?? '') }}</p>
    </div>

    <div class="footer-section">
        <h3>Quick Links</h3>
        <ul>
            @if($footerLinks->isNotEmpty())
                @foreach($footerLinks as $item)
                    <li>
                        <a href="{{ $item->resolved_url }}"
                           @if($item->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                            {{ $item->resolved_title }}
                        </a>
                    </li>
                @endforeach
            @else
                <li><a href="{{ url('/') }}">Home</a></li>
                <li><a href="{{ url('/blog') }}">Blog</a></li>
                <li><a href="{{ url('/about') }}">About</a></li>
                <li><a href="{{ url('/contact') }}">Contact</a></li>
            @endif
        </ul>
    </div>

    <div class="footer-section">
        <h3>Get in Touch</h3>
        <ul>
            @if($adminEmail)
                <li><a href="mailto:{{ $adminEmail }}">{{ $adminEmail }}</a></li>
            @endif
            <li><a href="{{ url('/search') }}">Search</a></li>
            <li><a href="{{ url('/admin') }}">Admin</a></li>
        </ul>
    </div>
</div>

<div class="container footer-bottom">
    <p>&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
</div>
