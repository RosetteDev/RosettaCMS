<?php

namespace App\Support;

/**
 * Curated starter templates for the page builder.
 *
 * Each pattern is a flat list of root blocks (container blocks with nested
 * children cannot be represented as a pattern because the editor operates on
 * top-level blocks only). Patterns are read-only: they are not persisted,
 * they only seed fresh copies into the canvas on insert.
 */
class BlockPatterns
{
    public static function all(): array
    {
        return [
            [
                'id' => 'hero-cta',
                'name' => 'Hero + CTA',
                'description' => 'Large heading, short lead-in text and a call-to-action button.',
                'icon' => 'fa-rocket',
                'blocks' => [
                    ['type' => 'heading', 'content' => ['level' => 'h1', 'text' => 'Build something great'], 'settings' => []],
                    ['type' => 'text', 'content' => ['text' => 'A short paragraph that introduces your product or message.'], 'settings' => []],
                    ['type' => 'button', 'content' => ['text' => 'Get started', 'url' => '#', 'style' => 'primary'], 'settings' => []],
                ],
            ],
            [
                'id' => 'feature-highlights',
                'name' => 'Feature Highlights',
                'description' => 'Heading, callout and button for a feature overview.',
                'icon' => 'fa-star',
                'blocks' => [
                    ['type' => 'heading', 'content' => ['level' => 'h2', 'text' => 'Why you will love it'], 'settings' => []],
                    ['type' => 'callout', 'content' => ['text' => 'The key benefit, stated once, clearly.', 'style' => 'info', 'icon' => ''], 'settings' => []],
                    ['type' => 'button', 'content' => ['text' => 'Learn more', 'url' => '#', 'style' => 'secondary'], 'settings' => []],
                ],
            ],
            [
                'id' => 'quote-strip',
                'name' => 'Quote Strip',
                'description' => 'A single highlighted quote for social proof.',
                'icon' => 'fa-quote-right',
                'blocks' => [
                    ['type' => 'quote', 'content' => ['text' => 'This changed how our team works.', 'cite' => 'A happy customer'], 'settings' => []],
                ],
            ],
            [
                'id' => 'announcement-callout',
                'name' => 'Announcement Callout',
                'description' => 'A prominent callout for a notice, launch or tip.',
                'icon' => 'fa-bullhorn',
                'blocks' => [
                    ['type' => 'callout', 'content' => ['text' => 'Announcing a new feature — see what is new.', 'style' => 'warning', 'icon' => ''], 'settings' => []],
                ],
            ],
            [
                'id' => 'gallery-showcase',
                'name' => 'Gallery Showcase',
                'description' => 'Heading plus a gallery for a visual showcase.',
                'icon' => 'fa-images',
                'blocks' => [
                    ['type' => 'heading', 'content' => ['level' => 'h2', 'text' => 'Gallery'], 'settings' => []],
                    ['type' => 'gallery', 'content' => ['images' => []], 'settings' => []],
                ],
            ],
            [
                'id' => 'spacer-rhythm',
                'name' => 'Spacer Rhythm',
                'description' => 'An empty section for vertical breathing room.',
                'icon' => 'fa-arrows-down-to-line',
                'blocks' => [
                    ['type' => 'spacer', 'content' => ['size' => 80], 'settings' => []],
                ],
            ],
        ];
    }

    /**
     * Find a single pattern by its id.
     */
    public static function find(string $id): ?array
    {
        foreach (self::all() as $pattern) {
            if ($pattern['id'] === $id) {
                return $pattern;
            }
        }

        return null;
    }
}
