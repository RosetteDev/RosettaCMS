<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class GalleryBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'gallery';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Gallery';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-images';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'images' => [],
        'columns' => 3,
        'gap' => 10,
        'link' => 'none', // none, file, attachment
        'size' => 'medium',
        'rounded' => false,
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.gallery';
    }

    /**
     * Render the block.
     *
     * @return string
     */
    public function render(): string
    {
        $images = $this->getAttribute('images', []);
        $columns = $this->getAttribute('columns', 3);
        $gap = $this->getAttribute('gap', 10);
        $link = $this->getAttribute('link', 'none');
        $rounded = $this->getAttribute('rounded', false);

        if (empty($images)) {
            return '<div class="text-muted text-center py-3"><i class="fas fa-images me-1"></i>No images in gallery</div>';
        }

        $columnsClass = match ($columns) {
            1 => 'col-12',
            2 => 'col-6 col-md-6',
            3 => 'col-6 col-md-4',
            4 => 'col-6 col-md-3',
            5 => 'col-4 col-md-2',
            6 => 'col-4 col-md-2',
            default => 'col-6',
        };

        $roundedClass = $rounded ? ' rounded' : '';

        $html = '<div class="gallery gallery-columns-'.$columns.' gallery-gap-'.$gap.' mb-3">';
        $html .= '<div class="row g-'.$gap.'">';

        foreach ($images as $image) {
            $imgUrl = $image['url'] ?? '';
            $imgAlt = $image['alt'] ?? '';
            $imgTitle = $image['title'] ?? '';

            if ($link === 'file') {
                $href = $imgUrl;
            } elseif ($link === 'attachment' && isset($image['attachment_id'])) {
                $href = '#';
            } else {
                $href = '#';
            }

            $html .= sprintf(
                '<div class="%s"><a href="%s" class="d-block"><img src="%s" alt="%s" title="%s" class="img-fluid%s"></a></div>',
                $columnsClass,
                e($href),
                e($imgUrl),
                e($imgAlt),
                e($imgTitle),
                $roundedClass
            );
        }

        $html .= '</div></div>';

        return $html;
    }
}
