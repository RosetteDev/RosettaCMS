<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteIpRule extends Model
{
    protected $fillable = [
        'site_id',
        'type',
        'cidr',
        'label',
    ];

    protected $casts = [
        'site_id' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
