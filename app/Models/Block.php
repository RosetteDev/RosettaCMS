<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'site_id',
        'parent_block_id',
        'type',
        'content',
        'settings',
        'order',
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
    ];

    /**
     * Get the post that owns the block.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the parent block.
     */
    public function parentBlock()
    {
        return $this->belongsTo(Block::class, 'parent_block_id');
    }

    /**
     * Get the child blocks.
     */
    public function childBlocks()
    {
        return $this->hasMany(Block::class, 'parent_block_id')->orderBy('order');
    }

    /**
     * Scope a query to only include blocks for a specific post.
     */
    public function scopeForPost($query, $postId)
    {
        return $query->where('post_id', $postId)
            ->whereNull('parent_block_id')
            ->orderBy('order');
    }

    /**
     * Get the block renderer class.
     */
    public function getRendererClass(): ?string
    {
        $blockTypes = [
            'text' => \RosettaCMS\Plugins\VisualEditor\Blocks\TextBlock::class,
            'heading' => \RosettaCMS\Plugins\VisualEditor\Blocks\HeadingBlock::class,
            'image' => \RosettaCMS\Plugins\VisualEditor\Blocks\ImageBlock::class,
            'columns' => \RosettaCMS\Plugins\VisualEditor\Blocks\ColumnsBlock::class,
            'button' => \RosettaCMS\Plugins\VisualEditor\Blocks\ButtonBlock::class,
            'divider' => \RosettaCMS\Plugins\VisualEditor\Blocks\DividerBlock::class,
            'spacer' => \RosettaCMS\Plugins\VisualEditor\Blocks\SpacerBlock::class,
            'video' => \RosettaCMS\Plugins\VisualEditor\Blocks\VideoBlock::class,
            'gallery' => \RosettaCMS\Plugins\VisualEditor\Blocks\GalleryBlock::class,
            'html' => \RosettaCMS\Plugins\VisualEditor\Blocks\HtmlBlock::class,
            'callout' => \RosettaCMS\Plugins\VisualEditor\Blocks\CalloutBlock::class,
            'quote' => \RosettaCMS\Plugins\VisualEditor\Blocks\QuoteBlock::class,
        ];

        return $blockTypes[$this->type] ?? null;
    }

    /**
     * Render the block.
     *
     * Several block types have no instantiable renderer (they are view-only),
     * so any failure to render falls back to a safe text representation of the
     * content instead of fataling the whole page.
     *
     * @param int $depth Recursion depth guard for nested container blocks
     * @return string
     */
    public function render(int $depth = 0): string
    {
        if ($depth > 12) {
            return '';
        }

        try {
            $rendererClass = $this->getRendererClass();

            if (!$rendererClass) {
                return '<div class="block block-'.e($this->type).'"><p>Unknown block type: '.e($this->type).'</p></div>';
            }

            $renderer = new $rendererClass();
            $renderer->setAttributes($this->content ?? []);
            // Only query child blocks for persisted models; unsaved (in-memory)
            // blocks render standalone to avoid a null-id lazy query.
            $children = $this->exists ? $this->childBlocks->all() : [];
            $renderer->setChildren($children, $depth);

            return $renderer->render();
        } catch (\Throwable $e) {
            $text = $this->content['text'] ?? $this->content['content'] ?? '';

            if (is_string($text) && trim($text) === '') {
                return '<div class="block block-'.e($this->type).'"></div>';
            }

            return '<div class="block block-'.e($this->type).'">'.nl2br(e((string) $text)).'</div>';
        }
    }

    /**
     * Get available block types as an associative array.
     *
     * @return array
     */
    public static function getTypeOptions(): array
    {
        return [
            'text' => 'Text Block',
            'heading' => 'Heading',
            'image' => 'Image',
            'columns' => 'Columns',
            'button' => 'Button',
            'divider' => 'Divider',
            'spacer' => 'Spacer',
            'video' => 'Video',
            'gallery' => 'Gallery',
            'html' => 'Custom HTML',
            'callout' => 'Callout Box',
            'quote' => 'Quote',
        ];
    }
}
