<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Webhook extends Model
{
    use HasFactory;

    /**
     * Events a webhook can subscribe to. Each value is the name of the
     * event the webhook is registered to receive.
     */
    public const ALLOWED_EVENTS = [
        'post.published',
        'post.updated',
        'page.published',
        'page.updated',
        'comment.created',
        'media.created',
    ];

    protected $fillable = [
        'site_id',
        'url',
        'secret',
        'events',
        'description',
        'active',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $casts = [
        'events' => 'array',
        'active' => 'boolean',
        'last_fired_at' => 'datetime',
    ];

    /**
     * Generate a new webhook for the given site.
     *
     * Returns an array with the plaintext secret (shown once) and the model.
     */
    public static function createWebhook(?int $siteId, string $url, array $events, ?string $description = null): array
    {
        $plaintext = 'whsec_' . Str::random(48);

        $webhook = static::create([
            'site_id' => $siteId,
            'url' => $url,
            'secret' => $plaintext,
            'events' => array_values(array_unique($events)),
            'description' => $description,
            'active' => true,
        ]);

        return ['webhook' => $webhook, 'secret' => $plaintext];
    }

    /**
     * Active webhooks for a site.
     */
    public function scopeActiveForSite($query, ?int $siteId)
    {
        return $query->where('active', true)
            ->where(function ($q) use ($siteId) {
                $q->whereNull('site_id');

                if ($siteId !== null) {
                    $q->orWhere('site_id', $siteId);
                }
            });
    }

    /**
     * Check whether the webhook subscribes to the given event.
     */
    public function subscribesTo(string $event): bool
    {
        return in_array($event, $this->events ?? [], true);
    }

    /**
     * The site that owns the webhook (null = site-agnostic, fires for all sites).
     */
    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Record the outcome of the last delivery attempt.
     */
    public function recordDelivery(?int $responseCode = null, ?string $error = null): void
    {
        $this->forceFill([
            'last_fired_at' => now(),
            'last_response_code' => $responseCode,
            'last_error' => $error !== null ? substr($error, 0, 500) : null,
        ])->saveQuietly();
    }
}
