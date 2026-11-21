@php
    $breadcrumbs = [];
    
    // Get current path segments
    $path = trim(parse_url(request()->url(), PHP_URL_PATH), '/');
    $segments = array_filter(explode('/', $path));
    
    // Home crumb
    $breadcrumbs[] = [
        'label' => 'Home',
        'url' => url('/')
    ];
    
    // Build path incrementally
    $currentPath = '';
    foreach ($segments as $segment) {
        $currentPath .= '/' . $segment;
        
        // Skip common system routes
        if (in_array($segment, ['admin', 'api', 'wp-json'])) {
            continue;
        }
        
        // Determine label based on route type
        $label = str_replace('-', ' ', $segment);
        $label = str_replace('_', ' ', $label);
        $label = ucwords($label);
        
        // Try to get title from database for known routes
        if (count($segments) === 1 && $segment === 'blog') {
            $label = 'Blog';
        } elseif (count($segments) === 1 && $segment === 'search') {
            $label = 'Search';
        }
        
        $breadcrumbs[] = [
            'label' => $label,
            'url' => url($currentPath)
        ];
    }
    
    // Add current page title if not home
    if ($path !== '' && isset($title)) {
        $breadcrumbs[count($breadcrumbs) - 1]['label'] = $title;
    }
@endphp

@if(count($breadcrumbs) > 1)
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol class="breadcrumb">
            @foreach($breadcrumbs as $index => $crumb)
                <li class="breadcrumb-item{{ $index === count($breadcrumbs) - 1 ? ' active' : '' }}">
                    @if($index === count($breadcrumbs) - 1)
                        {{ $crumb['label'] }}
                    @else
                        <a href="{{ $crumb['url'] }}" aria-current="{{ $index === count($breadcrumbs) - 2 ? 'page' : null }}">
                            {{ $crumb['label'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
