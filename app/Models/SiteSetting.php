<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'analytics_code',
        'custom_css',
        'custom_js',
        'maintenance_mode',
        'disable_comments',
        'post_per_page',
        'theme',
        'site_title',
        'site_description',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'disable_comments' => 'boolean',
        'post_per_page' => 'integer',
    ];

    /**
     * Get the site that owns the settings.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
