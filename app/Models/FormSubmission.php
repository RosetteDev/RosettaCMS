<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $table = 'form_submissions';

    protected $fillable = [
        'site_id',
        'form_id',
        'data',
        'ip_hash',
        'user_agent',
        'spam_score',
        'spam_reason',
        'status',
    ];

    protected $casts = [
        'data' => 'array',
        'spam_score' => 'integer',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }
}
