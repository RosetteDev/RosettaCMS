<?php

namespace RosettaCMS\Plugins\VisualEditor\Blocks;

use RosettaCMS\Plugins\VisualEditor\Block;

class VideoBlock extends Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type = 'video';

    /**
     * The block name.
     *
     * @var string
     */
    protected $name = 'Video';

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon = 'fas fa-video';

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [
        'source' => '',
        'poster' => '',
        'autoplay' => false,
        'loop' => false,
        'controls' => true,
        'muted' => false,
        'alignment' => 'center',
    ];

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.video';
    }

    /**
     * Render the block.
     *
     * @return string
     */
    public function render(): string
    {
        $source = $this->getAttribute('source', '');
        $poster = $this->getAttribute('poster', '');
        $autoplay = $this->getAttribute('autoplay', false) ? ' autoplay' : '';
        $loop = $this->getAttribute('loop', false) ? ' loop' : '';
        $controls = $this->getAttribute('controls', true) ? ' controls' : '';
        $muted = $this->getAttribute('muted', false) ? ' muted' : '';
        $alignment = $this->getAttribute('alignment', 'center');

        if (empty($source)) {
            return '<div class="text-muted text-center py-3"><i class="fas fa-video me-1"></i>No video source specified</div>';
        }

        return sprintf(
            '<div class="text-%s mb-3"><video class="rounded" poster="%s"%s%s%s%s><source src="%s"></video></div>',
            $alignment,
            e($poster),
            $autoplay,
            $loop,
            $controls,
            $muted,
            e($source)
        );
    }
}
