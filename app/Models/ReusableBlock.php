<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReusableBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_id',
        'name',
        'description',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Get the site that owns this reusable block (null = site-wide).
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * The root blocks stored in the template.
     *
     * @return array<int, array{type:string, content:array, settings:array}>
     */
    public function blocks(): array
    {
        return is_array($this->payload) ? $this->payload : [];
    }

    /**
     * Number of root blocks in the template.
     */
    public function blockCount(): int
    {
        return count($this->blocks());
    }
}
