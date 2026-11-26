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

    /**
     * Render the block.
     */
    public function render(): string
    {
        $columns = (int) $this->getAttribute('columns', 2);

        if ($columns < 1) {
            $columns = 1;
        }

        if ($columns > 6) {
            $columns = 6;
        }

        $html = '<div class="row g-3 mb-3">';

        for ($i = 0; $i < $columns; $i++) {
            $html .= '<div class="col" style="flex: 1 1 0;"></div>';
        }

        $html .= '</div>';

        return $html;
    }
}
