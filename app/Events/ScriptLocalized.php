<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScriptLocalized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $handle;
    public $objectName;
    public $l10n;

    /**
     * Create a new event instance.
     */
    public function __construct($handle, $objectName, $l10n)
    {
        $this->handle = $handle;
        $this->objectName = $objectName;
        $this->l10n = $l10n;
    }
}
