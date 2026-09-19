<?php

namespace App\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'company_name',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_id',
        'currency',
        'portal_access_token',
        'portal_token_expires_at',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'portal_token_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->portal_access_token)) {
                $client->portal_access_token = Str::random(60);
                $client->portal_token_expires_at = now()->addDays(30);
            }
            if (empty($client->currency)) {
                $client->currency = 'USD';
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    public function invoicePayments(): HasManyThrough
    {
        return $this->hasManyThrough(InvoicePayment::class, Invoice::class);
    }

    public function totalInvoiced(): float
    {
        return round((float) $this->invoices()->sum('total'), 2);
    }

    public function totalPaid(): float
    {
        return round((float) $this->invoices()->sum('amount_paid'), 2);
    }

    public function totalOutstanding(): float
    {
        return round((float) $this->invoices()->whereIn('status', ['sent', 'partially_paid', 'overdue'])->sum('balance_due'), 2);
    }

    public function getPortalUrlAttribute(): string
    {
        if (empty($this->portal_access_token) || ($this->portal_token_expires_at && $this->portal_token_expires_at->isPast())) {
            $this->update([
                'portal_access_token' => Str::random(60),
                'portal_token_expires_at' => now()->addDays(30),
            ]);
        }

        return route('portal.access', $this->portal_access_token);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return Currency::symbol($this->currency ?? 'USD');
    }
}
