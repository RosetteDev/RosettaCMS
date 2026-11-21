<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class HtmlBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'html';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Custom HTML';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-code';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'code' => '',
        'format' => 'html',
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.html';
    }

    /**
     * Render the block.
     *
     * @return string
     */
    public function render(): string
    {
        $code = $this->getAttribute('code', '');
        $format = $this->getAttribute('format', 'html');

        if (empty($code)) {
            return '<div class="text-muted text-center py-3"><i class="fas fa-code me-1"></i>No HTML code provided</div>';
        }

        if ($format === 'html') {
            return $code;
        }

        // For other formats, return as text
        return '<pre class="bg-dark text-light p-3 rounded"><code>'.e($code).'</code></pre>';
    }
}
