<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'parent_id',
        'title',
        'type',
        'page_id',
        'url',
        'target',
        'order',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function page()
    {
        return $this->belongsTo(Post::class, 'page_id');
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Resolve the final link for this item, whether it points at a page or a
     * custom URL.
     */
    public function getResolvedUrlAttribute(): string
    {
        if ($this->type === 'page' && $this->page) {
            return url('/pages/' . $this->page->slug);
        }

        if ($this->type === 'post' && $this->page) {
            return url('/posts/' . $this->page->slug);
        }

        if (!empty($this->url)) {
            return $this->url;
        }

        return '#';
    }
}
