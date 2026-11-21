<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class ButtonBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'button';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Button';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-hand-pointer';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'text' => 'Click Here',
        'url' => '',
        'style' => 'primary',
        'size' => 'medium',
        'alignment' => 'left',
        'target' => '_self',
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.button';
    }
}
