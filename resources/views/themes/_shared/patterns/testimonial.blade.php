@php
    /**
     * Reusable testimonial pattern.
     * @include('themes._shared.patterns.testimonial', ['quote' => '...', 'author' => '...', 'role' => '...'])
     */
    $quote = $quote ?? '';
    $author = $author ?? '';
    $role = $role ?? '';
@endphp
<figure class="pattern-testimonial">
    <blockquote>
        <p>&ldquo;{{ $quote }}&rdquo;</p>
    </blockquote>
    @if($author)
        <figcaption>
            <strong>{{ $author }}</strong>
            @if($role)<span>{{ $role }}</span>@endif
        </figcaption>
    @endif
</figure>
