<?php

namespace App\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Estimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'estimate_number',
        'estimate_date',
        'expiry_date',
        'status',
        'style',
        'logo_id',
        'currency',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'additional_charges',
        'additional_charges_total',
        'total',
        'notes',
        'terms',
        'public_token',
        'viewed_at',
        'accepted_at',
        'declined_at',
        'decline_reason',
        'converted_invoice_id',
    ];

    protected $casts = [
        'estimate_date' => 'date',
        'expiry_date' => 'date',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'additional_charges' => 'array',
        'additional_charges_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Estimate $estimate) {
            if (empty($estimate->public_token)) {
                $estimate->public_token = Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(UserLogo::class, 'logo_id');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class);
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function isInvoiced(): bool
    {
        return $this->status === 'invoiced' || ! is_null($this->converted_invoice_id);
    }

    public function markAsViewed(): void
    {
        if (is_null($this->viewed_at)) {
            $this->update(['viewed_at' => now()]);
        }
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'accepted' => 'emerald',
            'invoiced' => 'purple',
            'sent' => 'blue',
            'declined' => 'rose',
            default => 'slate',
        };
    }

    public function getPublicUrlAttribute(): string
    {
        return route('estimates.public', $this->public_token);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return Currency::symbol($this->currency ?? 'USD');
    }

    public function getFormattedTotalAttribute(): string
    {
        return Currency::format($this->total, $this->currency ?? 'USD');
    }

    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function decline(): void
    {
        $this->update([
            'status' => 'declined',
            'declined_at' => now(),
        ]);
    }
}
