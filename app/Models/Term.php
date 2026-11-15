<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxonomy_id',
        'name',
        'slug',
        'description',
        'parent',
        'order',
    ];

    /**
     * Get the taxonomy that owns the term.
     */
    public function taxonomy()
    {
        return $this->belongsTo(Taxonomy::class);
    }

    /**
     * Get the posts associated with the term.
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    /**
     * Get the parent term.
     */
    public function parent()
    {
        return $this->belongsTo(Term::class, 'parent');
    }

    /**
     * Get the children terms.
     */
    public function children()
    {
        return $this->hasMany(Term::class, 'parent');
    }
}
