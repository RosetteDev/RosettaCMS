<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Blocks\Block;

class QuoteBlock extends Block
{
    /**
     * Block type identifier
     */
    protected $type = 'quote';

    /**
     * Default settings for the block
     */
    protected $defaults = [
        'text' => '',
        'citation' => '',
        'alignment' => 'center',
        'style' => 'default',
    ];

    /**
     * Render the quote block
     */
    public function render(): string
    {
        $text = $this->getAttribute('text', '');
        $citation = $this->getAttribute('citation', '');
        $alignment = $this->getAttribute('alignment', 'center');
        $style = $this->getAttribute('style', 'default');

        $alignmentClass = $alignment === 'left' ? 'text-start' : ($alignment === 'right' ? 'text-end' : 'text-center');
        $styleClass = $style === 'minimal' ? 'quote-minimal' : 'quote-default';

        $quoteHtml = '<blockquote class="quote ' . $alignmentClass . ' ' . $styleClass . ' py-4 px-4 border-start border-5">';
        $quoteHtml .= '<p class="quote-text mb-3 fs-5">' . $this->parseText($text) . '</p>';
        
        if ($citation) {
            $quoteHtml .= '<footer class="quote-citation fw-semibold text-muted">' . e($citation) . '</footer>';
        }
        
        $quoteHtml .= '</blockquote>';

        return $quoteHtml;
    }
}
