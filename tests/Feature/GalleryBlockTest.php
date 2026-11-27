<?php

namespace Tests\Feature;

use RosettaCMS\Plugins\VisualEditor\Blocks\GalleryBlock;
use Tests\TestCase;

class GalleryBlockTest extends TestCase
{
    public function test_renders_plain_string_urls(): void
    {
        $block = new GalleryBlock();
        $block->setAttributes([
            'images' => ['https://example.com/a.jpg', 'https://example.com/b.jpg'],
            'columns' => 3,
        ]);

        $html = $block->render();

        $this->assertStringContainsString('src="https://example.com/a.jpg"', $html);
        $this->assertStringContainsString('src="https://example.com/b.jpg"', $html);
        $this->assertStringContainsString('gallery-columns-3', $html);
    }

    public function test_renders_associative_entries_with_alt_and_title(): void
    {
        $block = new GalleryBlock();
        $block->setAttributes([
            'images' => [
                ['url' => 'https://example.com/a.jpg', 'alt' => 'A custom alt', 'title' => 'A title'],
            ],
        ]);

        $html = $block->render();

        $this->assertStringContainsString('alt="A custom alt"', $html);
        $this->assertStringContainsString('title="A title"', $html);
    }

    public function test_empty_images_render_fallback(): void
    {
        $block = new GalleryBlock();
        $block->setAttributes(['images' => []]);

        $this->assertStringContainsString('No images in gallery', $block->render());
    }

    public function test_carousel_layout_renders_track_and_buttons(): void
    {
        $block = new GalleryBlock();
        $block->setAttributes([
            'images' => ['https://example.com/a.jpg', 'https://example.com/b.jpg'],
            'layout' => 'carousel',
        ]);

        $html = $block->render();

        $this->assertStringContainsString('gallery-carousel-track', $html);
        $this->assertStringContainsString('gallery-carousel-prev', $html);
        $this->assertStringContainsString('gallery-carousel-next', $html);
        $this->assertStringContainsString('role="list"', $html);
        $this->assertStringContainsString('data-gallery-carousel', $html);
    }

    public function test_grid_layout_is_default(): void
    {
        $block = new GalleryBlock();
        $block->setAttributes([
            'images' => ['https://example.com/a.jpg'],
        ]);

        $this->assertStringContainsString('gallery-columns-3', $block->render());
        $this->assertStringNotContainsString('gallery-carousel-track', $block->render());
    }

    public function test_fallback_alt_derived_from_filename(): void
    {
        $block = new GalleryBlock();
        $block->setAttributes([
            'images' => ['https://example.com/photos/summer-beach-photo.jpg'],
        ]);

        $this->assertStringContainsString('alt="Summer Beach Photo"', $block->render());
    }
}
