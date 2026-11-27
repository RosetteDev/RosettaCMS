<?php

namespace RosettaCMS\Plugins\VisualEditor;

abstract class Block
{
    /**
     * The block type.
     *
     * @var string
     */
    protected $type;

    /**
     * The block name.
     *
     * @var string
     */
    protected $name;

    /**
     * The block icon.
     *
     * @var string
     */
    protected $icon;

    /**
     * Default attributes for the block.
     *
     * @var array
     */
    protected $defaultAttributes = [];

    /**
     * Block attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Child blocks (for container blocks such as columns).
     *
     * @var array
     */
    protected $children = [];

    /**
     * Recursion depth, set by the parent renderer.
     *
     * @var int
     */
    protected $depth = 0;

    /**
     * Create a new block instance.
     */
    public function __construct()
    {
        $this->attributes = $this->defaultAttributes;
    }

    /**
     * Get the block type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the block name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the block icon.
     *
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get the block attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get a specific attribute.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Set block attributes.
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Set a specific attribute.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Set the child blocks (container blocks only) and the recursion depth.
     *
     * @param array $children Child Block models
     * @param int $depth Current recursion depth
     * @return $this
     */
    public function setChildren(array $children, int $depth = 0): self
    {
        $this->children = $children;
        $this->depth = $depth;
        return $this;
    }

    /**
     * Get the child blocks.
     *
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Render the block.
     *
     * The default implementation produces a safe text representation of the
     * block's content. Subclasses override this with a real renderer.
     *
     * @return string
     */
    public function render(): string
    {
        $text = $this->attributes['text'] ?? $this->attributes['content'] ?? $this->attributes['html'] ?? '';

        if (!is_string($text) || trim($text) === '') {
            return '';
        }

        return '<div class="block-render block-render-' . e($this->type) . '">' . $this->parseText($text) . '</div>';
    }

    /**
     * Get the block view.
     *
     * @return string
     */
    public function view(): string
    {
        return 'visual-editor::blocks.' . $this->type;
    }

    /**
     * Render plain text with line breaks preserved, HTML-escaped.
     */
    protected function parseText(string $text): string
    {
        return nl2br(e($text));
    }
}
