<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class HeadingBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'heading';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Heading';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-heading';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'level' => 'h2',
        'content' => '',
        'alignment' => 'left',
        'text_color' => '#1f2937',
        'font_weight' => 'bold',
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.heading';
    }
}
