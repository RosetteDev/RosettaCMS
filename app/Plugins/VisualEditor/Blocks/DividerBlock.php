<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class DividerBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'divider';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Divider';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-minus';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'style' => 'solid',
        'color' => '#e5e7eb',
        'thickness' => 1,
        'margin' => 20,
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.divider';
    }

    /**
     * Render the block.
     */
    public function render(): string
    {
        $style = $this->getAttribute('style', 'solid');
        $thickness = (int) $this->getAttribute('thickness', 1);

        if (!in_array($style, ['solid', 'dashed', 'dotted'], true)) {
            $style = 'solid';
        }

        if ($thickness < 1) {
            $thickness = 1;
        }

        return '<hr style="border-top: ' . $thickness . 'px ' . $style . ' #dee2e6;">';
    }
}
