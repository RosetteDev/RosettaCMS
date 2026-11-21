<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StyleEnqueued
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $handle;
    public $src;
    public $deps;
    public $ver;
    public $media;

    /**
     * Create a new event instance.
     */
    public function __construct($handle, $src = '', $deps = [], $ver = false, $media = 'all')
    {
        $this->handle = $handle;
        $this->src = $src;
        $this->deps = $deps;
        $this->ver = $ver;
        $this->media = $media;
    }
}
