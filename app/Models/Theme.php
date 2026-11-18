<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'description',
        'author',
        'author_url',
        'screenshot',
        'is_active',
        'is_default',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the sites using this theme.
     */
    public function sites()
    {
        return $this->hasMany(Site::class, 'theme');
    }
}
