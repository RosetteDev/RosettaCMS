<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class ColumnsBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'columns';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Columns';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-columns';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'columns' => 2,
        'gutter' => 20,
        'content' => [],
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.columns';
    }
}
