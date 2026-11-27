@extends('themes.business.layout')

@section('content')
    <!-- Hero (shared pattern) -->
    @include('themes._shared.patterns.hero', [
        'title' => $settings->resolved_site_title ?? ($site->name ?? 'Business'),
        'subtitle' => $settings->resolved_site_description ?? 'We help teams ship reliable, well-designed products — fast.',
        'buttonText' => 'Get a Quote',
        'buttonUrl' => url('/contact'),
    ])

    <!-- Services (shared pattern) -->
    @include('themes._shared.patterns.feature-grid', [
        'heading' => 'What we do',
        'features' => [
            ['icon' => '&#9878;', 'title' => 'Strategy & Planning', 'text' => 'Clear roadmaps, positioning and go-to-market plans built around your goals.'],
            ['icon' => '&#9638;', 'title' => 'Design & Build', 'text' => 'Modern, responsive websites and product interfaces designed to convert.'],
            ['icon' => '&#10004;', 'title' => 'Support & Growth', 'text' => 'Ongoing maintenance, performance work and content that keeps you found.'],
        ],
    ])

    <!-- Testimonial (shared pattern) -->
    @include('themes._shared.patterns.testimonial', [
        'quote' => 'The team delivered a site our customers actually like. Leads are up and the old phone system is quiet.',
        'author' => 'Dana Whitfield',
        'role' => 'Owner, Whitfield & Co.',
    ])

    <!-- Team -->
    <section class="section" style="background:#fff;">
        <div class="container">
            <div class="section-title">
                <h2>Meet the team</h2>
                <p>The people behind the work</p>
            </div>
            <div class="team-grid">
                <div class="team-member">
                    <div class="avatar">KM</div>
                    <h3>K. Mason</h3>
                    <p>Founder &amp; Strategy</p>
                </div>
                <div class="team-member">
                    <div class="avatar">AR</div>
                    <h3>A. Rivera</h3>
                    <p>Design Lead</p>
                </div>
                <div class="team-member">
                    <div class="avatar">JT</div>
                    <h3>J. Tan</h3>
                    <p>Engineering</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest posts -->
    @if($posts->isNotEmpty())
        <section class="section" style="background:#f8fafc;">
            <div class="container">
                <div class="section-title">
                    <h2>Latest from the blog</h2>
                    <p>Insights, notes and updates</p>
                </div>
                <div class="posts-grid">
                    @foreach($posts->take(3) as $post)
                        <article class="card">
                            @if($post->featured_image_url)
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
                            @endif
                            <div class="card-body">
                                <div class="post-meta"><span>{{ $post->published_at?->format('F j, Y') ?? $post->created_at->format('F j, Y') }}</span></div>
                                <h3><a href="{{ url('/posts/' . $post->slug) }}">{{ $post->title }}</a></h3>
                                <p>{{ Str::limit($post->plain_excerpt, 140) }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div style="text-align:center;margin-top:2rem;">
                    <a href="{{ url('/blog') }}" class="btn btn-primary">View all posts</a>
                </div>
            </div>
        </section>
    @endif

    <style>
        .team-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        .team-member { text-align: center; padding: 1.75rem 1rem; background: #f8fafc; border-radius: 0.75rem; border: 1px solid #e2e8f0; }
        .team-member .avatar { width: 72px; height: 72px; border-radius: 50%; background: #1e3a5f; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; margin: 0 auto 0.9rem; font-family: -apple-system, sans-serif; }
        .team-member h3 { font-size: 1.1rem; margin-bottom: 0.25rem; }
        .team-member p { color: #64748b; font-family: -apple-system, sans-serif; font-size: 0.85rem; }
        @media (max-width: 768px) { .team-grid { grid-template-columns: 1fr; } }
    </style>

    <!-- CTA band (shared pattern) -->
    @include('themes._shared.patterns.cta', [
        'title' => 'Ready to get started?',
        'text' => 'Tell us what you need and we will get back to you within one business day.',
        'buttonText' => 'Contact us',
        'buttonUrl' => url('/contact'),
    ])
@endsection
