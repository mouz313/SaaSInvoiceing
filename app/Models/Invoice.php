<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'invoice_number',
        'public_token',
        'invoice_date',
        'due_date',
        'status',
        'viewed_at',
        'paid_at',
        'stripe_payment_intent_id',
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
        'payment_instructions',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->public_token)) {
                $invoice->public_token = Str::random(40);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'viewed_at' => 'datetime',
            'paid_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'additional_charges' => 'array',
            'additional_charges_total' => 'decimal:2',
            'total' => 'decimal:2',
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

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(UserLogo::class, 'logo_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'emerald',
            'sent' => 'blue',
            'overdue' => 'rose',
            default => 'slate',
        };
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markAsViewed(): void
    {
        if (is_null($this->viewed_at)) {
            $this->update(['viewed_at' => now()]);
        }
    }

    public function markAsPaid(?string $paymentIntentId = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'stripe_payment_intent_id' => $paymentIntentId ?? $this->stripe_payment_intent_id,
        ]);
    }

    public function getPublicUrlAttribute(): string
    {
        return route('invoices.public', $this->public_token);
    }
}
