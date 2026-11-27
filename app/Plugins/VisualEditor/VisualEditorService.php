<?php

namespace RosettaCMS\Plugins\VisualEditor;

class VisualEditorService
{
    /**
     * Get the editor configuration.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'modes' => ['block', 'inline', 'mixed'],
            'defaultMode' => 'mixed',
            'blocks' => $this->getAvailableBlocks(),
            'tools' => $this->getAvailableTools(),
        ];
    }

    /**
     * Get available editor blocks.
     *
     * @return array
     */
    public function getAvailableBlocks(): array
    {
        return [
            'text' => [
                'name' => 'Text Block',
                'icon' => 'fas fa-paragraph',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\TextBlock::class,
            ],
            'heading' => [
                'name' => 'Heading',
                'icon' => 'fas fa-heading',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\HeadingBlock::class,
            ],
            'image' => [
                'name' => 'Image',
                'icon' => 'fas fa-image',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\ImageBlock::class,
            ],
            'gallery' => [
                'name' => 'Gallery',
                'icon' => 'fas fa-images',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\GalleryBlock::class,
            ],
            'video' => [
                'name' => 'Video',
                'icon' => 'fas fa-video',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\VideoBlock::class,
            ],
            'button' => [
                'name' => 'Button',
                'icon' => 'fas fa-hand-pointer',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\ButtonBlock::class,
            ],
            'divider' => [
                'name' => 'Divider',
                'icon' => 'fas fa-minus',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\DividerBlock::class,
            ],
            'html' => [
                'name' => 'HTML',
                'icon' => 'fas fa-code',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\HtmlBlock::class,
            ],
            'columns' => [
                'name' => 'Columns',
                'icon' => 'fas fa-columns',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\ColumnsBlock::class,
            ],
            'spacer' => [
                'name' => 'Spacer',
                'icon' => 'fas fa-arrows-alt-v',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\SpacerBlock::class,
            ],
            'callout' => [
                'name' => 'Callout Box',
                'icon' => 'fas fa-exclamation-circle',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\CalloutBlock::class,
            ],
            'quote' => [
                'name' => 'Quote',
                'icon' => 'fas fa-quote-left',
                'class' => \RosettaCMS\Plugins\VisualEditor\Blocks\QuoteBlock::class,
            ],
        ];
    }

    /**
     * Get available editor tools.
     *
     * @return array
     */
    public function getAvailableTools(): array
    {
        return [
            'style' => [
                'name' => 'Text Style',
                'icon' => 'fas fa-font',
                'class' => \RosettaCMS\Plugins\VisualEditor\Tools\StyleTool::class,
            ],
            'color' => [
                'name' => 'Color Picker',
                'icon' => 'fas fa-palette',
                'class' => \RosettaCMS\Plugins\VisualEditor\Tools\ColorTool::class,
            ],
            'link' => [
                'name' => 'Link',
                'icon' => 'fas fa-link',
                'class' => \RosettaCMS\Plugins\VisualEditor\Tools\LinkTool::class,
            ],
            'align' => [
                'name' => 'Alignment',
                'icon' => 'fas fa-align-left',
                'class' => \RosettaCMS\Plugins\VisualEditor\Tools\AlignTool::class,
            ],
        ];
    }
}
