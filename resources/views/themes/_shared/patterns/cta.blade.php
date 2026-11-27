@php
    /**
     * Reusable call-to-action band.
     * @include('themes._shared.patterns.cta', ['title' => '...', 'text' => '...', 'buttonText' => '...', 'buttonUrl' => '...'])
     */
    $title = $title ?? 'Get started today';
    $text = $text ?? '';
    $buttonText = $buttonText ?? 'Get Started';
    $buttonUrl = $buttonUrl ?? '/contact';
@endphp
<section class="pattern-cta">
    <div class="container pattern-cta-inner">
        <h2>{{ $title }}</h2>
        @if($text)
            <p>{{ $text }}</p>
        @endif
        <div class="pattern-cta-actions">
            <a href="{{ $buttonUrl }}" class="pattern-btn pattern-btn-primary">{{ $buttonText }}</a>
        </div>
    </div>
</section>
