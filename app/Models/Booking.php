<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'site_id',
        'service',
        'name',
        'email',
        'phone',
        'date',
        'time',
        'notes',
        'status',
        'ip_hash',
        'user_agent',
    ];

    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}
