<?php

namespace RosettaCMS\Plugins\Forms;

use RosettaCMS\Plugin;

class FormsPlugin extends Plugin
{
    protected $name = 'Forms';

    protected $description = 'Contact Form 7-style form builder: create forms, embed them on any page, and receive submissions with spam filtering and mail notifications.';

    protected $version = '1.0.0';

    protected $author = 'Rosetta CMS Team';

    protected $homepage = 'https://github.com/RosetteDev/RosettaCMS';

    /**
     * Bootstrap the plugin.
     */
    public function bootstrap(): void
    {
        $this->app->register(FormsServiceProvider::class);
    }
}
