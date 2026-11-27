<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'ip',
        'user_agent',
    ];

    protected $hidden = ['user_agent'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an activity entry.
     */
    public static function record(
        string $action,
        ?string $description = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?int $userId = null,
    ): void {
        try {
            $request = request();

            static::create([
                'user_id' => $userId ?? ($request?->user()?->id),
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'description' => $description,
                'ip' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Audit logging must never break the request.
            report($e);
        }
    }
}
