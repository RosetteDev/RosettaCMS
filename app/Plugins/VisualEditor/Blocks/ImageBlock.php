<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class ImageBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'image';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Image';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-image';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'url' => '',
        'alt' => '',
        'caption' => '',
        'alignment' => 'center',
        'link' => '',
        'rounded' => false,
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.image';
    }
}
