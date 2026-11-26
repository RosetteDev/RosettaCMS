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

    /**
     * Render the block.
     */
    public function render(): string
    {
        $image = $this->getAttribute('image', '') ?: $this->getAttribute('url', '');
        $caption = $this->getAttribute('caption', '');
        $showCaption = $this->getAttribute('showCaption', false);

        if (!is_string($image) || trim($image) === '') {
            return '';
        }

        $html = '<figure class="block-image mb-3"><img src="' . e($image) . '" alt="' . e($caption ?: '') . '" class="img-fluid rounded">';

        if ($showCaption && $caption) {
            $html .= '<figcaption class="text-muted small text-center mt-2">' . e($caption) . '</figcaption>';
        }

        $html .= '</figure>';

        return $html;
    }
}
