<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLock extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'locked_at',
        'heartbeat_at',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'heartbeat_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
