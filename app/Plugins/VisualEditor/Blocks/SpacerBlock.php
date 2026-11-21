<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class SpacerBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'spacer';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Spacer';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-arrows-alt-v';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'height' => 40,
        'mobile_height' => 20,
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.spacer';
    }
}
