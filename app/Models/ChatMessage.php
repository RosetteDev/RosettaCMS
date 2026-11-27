<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages';

    protected $fillable = [
        'site_id',
        'session_id',
        'sender',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function isVisitor(): bool
    {
        return $this->sender === 'visitor';
    }

    public function isAdmin(): bool
    {
        return $this->sender === 'admin';
    }
}
