<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Taxonomy extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'type',
        'description',
    ];

    /**
     * Get the site that owns the taxonomy.
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Get the terms for the taxonomy.
     */
    public function terms()
    {
        return $this->hasMany(Term::class);
    }

    /**
     * Scope a query to only include taxonomies of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
