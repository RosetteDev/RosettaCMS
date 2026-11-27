<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $table = 'contact_submissions';

    protected $fillable = [
        'site_id',
        'name',
        'email',
        'subject',
        'message',
        'custom_fields',
        'ip_hash',
        'user_agent',
        'spam_score',
        'spam_reason',
        'status',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'spam_score' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function getIsSpamAttribute(): bool
    {
        return $this->status === 'spam' || $this->spam_score >= 50;
    }
}
