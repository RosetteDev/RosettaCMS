@php
    /**
     * Render enabled custom widgets for a frontend location.
     * Usage: @include('themes._shared.widget-area', ['location' => 'footer'])
     */
    $location = $location ?? 'footer';
    $widgets = \App\Models\CustomWidget::enabled()
        ->where('location', $location)
        ->orderBy('priority')
        ->get();
@endphp

@if($widgets->isNotEmpty())
    <div class="widget-area widget-area-{{ $location }}">
        @foreach($widgets as $widget)
            <div class="widget widget-{{ $widget->type }}">
                @if($widget->name)
                    <h4 class="widget-title">{{ $widget->name }}</h4>
                @endif
                <div class="widget-content">{!! $widget->content !!}</div>
            </div>
        @endforeach
    </div>
@endif
