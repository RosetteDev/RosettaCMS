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
     * Render the block.
     *
     * @return string
     */
    abstract public function render(): string;

    /**
     * Get the block view.
     *
     * @return string
     */
    abstract public function view(): string;
}
