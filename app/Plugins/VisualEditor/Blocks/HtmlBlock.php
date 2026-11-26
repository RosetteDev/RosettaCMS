<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use Mews\Purifier\Facades\Purifier;
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
        $code = $this->getAttribute('code', '') ?: $this->getAttribute('html', '') ?: $this->getAttribute('content', '');
        $format = $this->getAttribute('format', 'html');

        if (empty($code)) {
            return '<div class="text-muted text-center py-3"><i class="fas fa-code me-1"></i>No HTML code provided</div>';
        }

        if ($format === 'html') {
            return Purifier::clean($code, [
                'HTML.Allowed' => 'p,br,strong,b,em,i,u,s,ul,ol,li,a[href|title],div,span,hr,img[src|alt|title|width|height],table,thead,tbody,tr,th,td',
                'Attr.EnableID' => true,
            ]);
        }

        // For other formats, return as text
        return '<pre class="bg-dark text-light p-3 rounded"><code>'.e($code).'</code></pre>';
    }
}
