@php
    $blockContent = $block->content ?? [];
@endphp

<div class="block-item" 
     data-block-id="{{ $block->id }}"
     data-block-type="{{ $block->type }}"
     draggable="true">
    
    <div class="block-type-label text-capitalize">{{ $block->type }}</div>
    
    <div class="block-controls">
        <button type="button" class="btn btn-sm btn-outline-primary" title="Move up">
            <i class="fas fa-chevron-up"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary" title="Move down">
            <i class="fas fa-chevron-down"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete">
            <i class="fas fa-trash"></i>
        </button>
    </div>
    
    <div class="block-content-editable" contenteditable="true">
        @if($block->type === 'text' || $block->type === 'heading')
            {{ $blockContent['text'] ?? '' }}
        @elseif($block->type === 'callout')
            <div class="callout-{{ $blockContent['style'] ?? 'primary' }}">
                {{ $blockContent['text'] ?? '' }}
            </div>
        @elseif($block->type === 'divider')
            <hr style="border-top: {{ $blockContent['thickness'] ?? 1 }}px {{ $blockContent['style'] ?? 'solid' }} #dee2e6;">
        @elseif($block->type === 'image')
            @if($blockContent['image'] ?? '')
                <img src="{{ $blockContent['image'] }}" alt="{{ $blockContent['caption'] ?? '' }}" style="max-width:100%;border-radius:0.375rem;">
            @endif
        @else
            <div class="text-muted">[{{ ucfirst($block->type) }} block content]</div>
        @endif
    </div>
</div>
