<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScriptEnqueued
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $handle;
    public $src;
    public $deps;
    public $ver;
    public $inFooter;

    /**
     * Create a new event instance.
     */
    public function __construct($handle, $src = '', $deps = [], $ver = false, $inFooter = false)
    {
        $this->handle = $handle;
        $this->src = $src;
        $this->deps = $deps;
        $this->ver = $ver;
        $this->inFooter = $inFooter;
    }
}
