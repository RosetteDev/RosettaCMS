@php
    /**
     * Reusable hero pattern.
     * @include('themes._shared.patterns.hero', ['title' => '...', 'subtitle' => '...', 'buttonText' => '...', 'buttonUrl' => '...'])
     */
    $title = $title ?? 'Welcome';
    $subtitle = $subtitle ?? '';
    $buttonText = $buttonText ?? null;
    $buttonUrl = $buttonUrl ?? null;
    $align = $align ?? 'center';
@endphp
<section class="pattern-hero" data-align="{{ $align }}">
    <div class="pattern-hero-inner container">
        <h1 class="pattern-hero-title">{{ $title }}</h1>
        @if($subtitle)
            <p class="pattern-hero-subtitle">{{ $subtitle }}</p>
        @endif
        @if($buttonText && $buttonUrl)
            <div class="pattern-hero-actions">
                <a href="{{ $buttonUrl }}" class="pattern-btn pattern-btn-primary">{{ $buttonText }}</a>
            </div>
        @endif
    </div>
</section>
