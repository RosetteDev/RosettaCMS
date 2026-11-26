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

    /**
     * Render the block.
     */
    public function render(): string
    {
        $text = $this->getAttribute('text', '');
        $level = $this->getAttribute('level', 'h2');

        if (!is_string($text) || trim($text) === '') {
            return '';
        }

        if (!preg_match('/^h[1-6]$/', (string) $level)) {
            $level = 'h2';
        }

        return '<' . $level . ' class="block-heading">' . e($text) . '</' . $level . '>';
    }
}
