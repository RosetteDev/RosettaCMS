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
     *
     * Children are distributed round-robin across the columns, preserving
     * order within each column.
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

        $children = $this->getChildren();

        $columnsHtml = array_fill(0, $columns, []);

        foreach ($children as $index => $child) {
            $columnsHtml[$index % $columns][] = $child;
        }

        $html = '<div class="row g-3 mb-3">';

        foreach ($columnsHtml as $columnChildren) {
            $html .= '<div class="col" style="flex: 1 1 0;">';

            foreach ($columnChildren as $child) {
                $html .= $child->render($this->depth + 1);
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
