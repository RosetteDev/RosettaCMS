<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Blocks\Block;

class CalloutBlock extends Block
{
    /**
     * Block type identifier
     */
    protected $type = 'callout';

    /**
     * Default settings for the block
     */
    protected $defaults = [
        'text' => '',
        'style' => 'primary',
        'icon' => '',
    ];

    /**
     * Render the callout block
     */
    public function render(): string
    {
        $text = $this->getAttribute('text', '');
        $style = $this->getAttribute('style', 'primary');
        $icon = $this->getAttribute('icon', '');

        $styles = [
            'primary' => 'border-primary',
            'secondary' => 'border-secondary',
            'success' => 'border-success',
            'danger' => 'border-danger',
            'warning' => 'border-warning',
            'info' => 'border-info',
        ];

        $styleClass = $styles[$style] ?? 'border-primary';
        $bgClass = 'bg-' . $style . '20';

        $iconHtml = $icon ? '<i class="' . e($icon) . ' me-2"></i>' : '';

        return '<div class="callout ' . $bgClass . ' ' . $styleClass . ' border-start border-4 py-3 px-4 rounded">
            ' . $iconHtml . '<div class="callout-content">' . $this->parseText($text) . '</div>
        </div>';
    }
}
