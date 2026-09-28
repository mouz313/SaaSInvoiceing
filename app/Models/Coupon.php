<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'applies_to',
        'min_spend',
        'max_discount',
        'max_uses',
        'times_used',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_spend' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'max_uses' => 'integer',
            'times_used' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeForUser(Builder $query, int|User $user): Builder
    {
        $userId = $user instanceof User ? $user->id : $user;

        return $query->where('user_id', $userId);
    }

    public function scopePlatform(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('times_used', '<', 'max_uses');
            });
    }

    /**
     * Check if coupon is currently valid for given amount, target type, and optional client.
     */
    public function isValid(?float $amount = null, ?string $targetType = null, ?int $clientId = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->times_used >= $this->max_uses) {
            return false;
        }

        if ($targetType && $this->applies_to !== 'all' && $this->applies_to !== $targetType) {
            return false;
        }

        if ($this->client_id && $clientId !== null && (int) $this->client_id !== (int) $clientId) {
            return false;
        }

        if ($amount !== null && $this->min_spend && $amount < (float) $this->min_spend) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount against a subtotal.
     */
    public function calculateDiscount(float $amount): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        $discount = 0.0;

        if ($this->discount_type === 'percentage') {
            $discount = round(($amount * (float) $this->discount_value) / 100, 2);
        } else {
            // Fixed amount
            $discount = min($amount, (float) $this->discount_value);
        }

        if ($this->max_discount && $discount > (float) $this->max_discount) {
            $discount = (float) $this->max_discount;
        }

        return round($discount, 2);
    }

    /**
     * Record a coupon usage and increment usage counter.
     */
    public function recordUsage(int|User $user, ?Model $usable = null, float $discount = 0.0): CouponUsage
    {
        $userId = $user instanceof User ? $user->id : $user;

        $this->increment('times_used');

        return $this->usages()->create([
            'user_id' => $userId,
            'usable_type' => $usable ? get_class($usable) : null,
            'usable_id' => $usable?->getKey(),
            'discount_amount' => $discount,
        ]);
    }
}
