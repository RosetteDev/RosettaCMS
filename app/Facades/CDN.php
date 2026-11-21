<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isCDNEnabled()
 * @method static string|null getCDNUrl()
 * @method static string cdnUrl(string $path)
 * @method static string cdnStorageUrl(string $path, string $disk = 'public')
 * @method static bool isCDNConfigured()
 */
class CDN extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'cdn';
    }
}
