@php
    $blockContent = $block->content ?? [];
    $isEditable = in_array($block->type, ['text', 'heading', 'callout', 'quote'], true);
@endphp

<div class="block-item"
     data-block-id="{{ $block->id }}"
     data-block-type="{{ $block->type }}"
     data-block-content="{{ json_encode($blockContent) }}"
     draggable="true">

    <div class="block-type-label text-capitalize">{{ $block->type }}</div>

    <div class="block-controls">
        <button type="button" class="btn btn-sm btn-outline-primary" title="Move up" onclick="moveBlock('{{ $block->id }}', 'up')">
            <i class="fas fa-chevron-up"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary" title="Move down" onclick="moveBlock('{{ $block->id }}', 'down')">
            <i class="fas fa-chevron-down"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteBlock('{{ $block->id }}')">
            <i class="fas fa-trash"></i>
        </button>
    </div>

    <div class="block-content-editable" contenteditable="{{ $isEditable ? 'true' : 'false' }}">
        @if($block->type === 'text' || $block->type === 'heading' || $block->type === 'quote')
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
        @elseif($block->type === 'html')
            <pre class="mb-0" style="white-space:pre-wrap;font-size:0.85rem;">{{ $blockContent['html'] ?? $blockContent['content'] ?? '' }}</pre>
        @else
            <div class="text-muted">[{{ ucfirst($block->type) }} block content]</div>
        @endif
    </div>
</div>
