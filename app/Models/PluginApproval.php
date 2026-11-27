<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PluginApproval extends Model
{
    protected $table = 'plugin_approvals';

    public $timestamps = false;

    protected $fillable = [
        'plugin_id',
        'permission',
        'user_id',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
