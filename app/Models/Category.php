<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'description',
        'color',
        'order',
    ];

    protected $casts = [
        'site_id' => 'integer',
        'order' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'category_post');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
