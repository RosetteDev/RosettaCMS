{{-- Render a post's blocks. Each block is rendered via its model render()
     method, which falls back to a safe text representation for block types
     whose renderer is not instantiable. --}}
@if(isset($post) && $post && $post->blocks && $post->blocks->isNotEmpty())
    @foreach($post->blocks as $block)
        <div class="block block-{{ $block->type }}" data-block-id="{{ $block->id }}">
            {!! $block->render() !!}
        </div>
    @endforeach
@endif
