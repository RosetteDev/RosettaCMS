@php
    /**
     * Reusable feature grid pattern.
     * @include('themes._shared.patterns.feature-grid', ['features' => [['icon' => '...', 'title' => '...', 'text' => '...'], ...]])
     */
    $features = $features ?? [];
    $heading = $heading ?? null;
@endphp
<section class="pattern-feature-grid">
    <div class="container">
        @if($heading)
            <div class="pattern-feature-grid-heading">
                <h2>{{ $heading }}</h2>
            </div>
        @endif
        <div class="pattern-feature-grid-items">
            @foreach($features as $feature)
                <div class="pattern-feature-item">
                    @if(!empty($feature['icon']))
                        <div class="pattern-feature-icon">{{ $feature['icon'] }}</div>
                    @endif
                    <h3>{{ $feature['title'] ?? '' }}</h3>
                    <p>{{ $feature['text'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
