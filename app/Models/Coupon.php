<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'site_id',
        'code',
        'type',
        'value',
        'min_subtotal',
        'max_uses',
        'used_count',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_subtotal' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Whether this coupon can be applied right now.
     */
    public function isUsable(): bool
    {
        if (!$this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * The discount amount for a given subtotal (capped at the subtotal).
     */
    public function discountFor(float $subtotal): float
    {
        $amount = $this->type === self::TYPE_PERCENT
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return min(round($amount, 2), round($subtotal, 2));
    }

    /**
     * Whether the coupon meets its minimum-subtotal requirement.
     */
    public function meetsMinimum(float $subtotal): bool
    {
        return $this->min_subtotal === null || $subtotal >= (float) $this->min_subtotal;
    }
}
