@php
    $menu = $menu ?? null;
    $items = $menu ? $menu->rootItems : collect();
    $navClass = $navClass ?? 'nav-menu';
@endphp

<ul class="{{ $navClass }}">
    @if($items->isNotEmpty())
        @foreach($items as $item)
            @php
                $children = $item->relationLoaded('children')
                    ? $item->children
                    : $item->children()->get();
            @endphp
            <li class="{{ $children->isNotEmpty() ? 'menu-item-has-children' : '' }}">
                <a href="{{ $item->resolved_url }}"
                   @if($item->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                    {{ $item->title }}
                </a>

                @if($children->isNotEmpty())
                    <ul class="submenu">
                        @foreach($children as $child)
                            <li>
                                <a href="{{ $child->resolved_url }}"
                                   @if($child->target === '_blank') target="_blank" rel="noopener noreferrer" @endif>
                                    {{ $child->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    @else
        <li><a href="{{ url('/') }}">Home</a></li>
        <li><a href="{{ url('/blog') }}">Blog</a></li>
        <li><a href="{{ url('/about') }}">About</a></li>
        <li><a href="{{ url('/contact') }}">Contact</a></li>
    @endif
</ul>

<style>
    nav li { position: relative; }
    nav .submenu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 50;
        min-width: 200px;
        margin: 0;
        padding: .5rem 0;
        list-style: none;
        background: #1f2937;
        border-radius: .5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .2);
    }
    nav li:hover > .submenu { display: block; }
    nav .submenu a {
        display: block;
        padding: .5rem 1rem;
        white-space: nowrap;
        background: transparent;
    }
    nav .submenu a:hover { background: rgba(255, 255, 255, .1); }
</style>
