<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class TextBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'text';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Text';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-paragraph';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'content' => '',
        'alignment' => 'left',
        'font_size' => 'normal',
        'text_color' => '#333333',
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.text';
    }
}
