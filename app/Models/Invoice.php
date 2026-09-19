<?php

namespace App\Models;

use App\Mail\PaymentReceiptMail;
use App\Support\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        'recurring_invoice_id',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_rate',
        'discount_amount',
        'additional_charges',
        'additional_charges_total',
        'total',
        'amount_paid',
        'balance_due',
        'last_reminder_sent_at',
        'reminder_count',
        'notes',
        'payment_instructions',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->public_token)) {
                $invoice->public_token = Str::random(40);
            }
            if (is_null($invoice->amount_paid)) {
                $invoice->amount_paid = 0;
            }
            if (is_null($invoice->balance_due)) {
                $invoice->balance_due = $invoice->total ?? 0;
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
            'last_reminder_sent_at' => 'datetime',
            'reminder_count' => 'integer',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'additional_charges' => 'array',
            'additional_charges_total' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'balance_due' => 'decimal:2',
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

    public function estimate(): HasOne
    {
        return $this->hasOne(Estimate::class, 'converted_invoice_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('paid_at', 'desc');
    }

    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'emerald',
            'partially_paid' => 'amber',
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

    public function recordPayment(
        float $amount,
        string $paymentMethod = 'other',
        ?string $referenceNumber = null,
        ?string $notes = null,
        mixed $paidAt = null
    ): InvoicePayment {
        $payment = $this->payments()->create([
            'user_id' => $this->user_id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'reference_number' => $referenceNumber,
            'notes' => $notes,
            'paid_at' => $paidAt ?? now(),
        ]);

        $newAmountPaid = round((float) $this->payments()->sum('amount'), 2);
        $newBalanceDue = max(0, round((float) $this->total - $newAmountPaid, 2));

        $newStatus = $this->status;
        $justBecamePaid = false;

        if ($newBalanceDue <= 0) {
            if ($this->status !== 'paid') {
                $justBecamePaid = true;
            }
            $newStatus = 'paid';
            $this->paid_at = now();
        } elseif ($newAmountPaid > 0) {
            $newStatus = 'partially_paid';
        }

        $this->update([
            'amount_paid' => $newAmountPaid,
            'balance_due' => $newBalanceDue,
            'status' => $newStatus,
            'paid_at' => $newBalanceDue <= 0 ? ($this->paid_at ?? now()) : $this->paid_at,
        ]);

        if ($justBecamePaid && $this->client?->email) {
            try {
                Mail::to($this->client->email)
                    ->send(new PaymentReceiptMail($this, ucfirst(str_replace('_', ' ', $paymentMethod))));
            } catch (\Throwable $e) {
                Log::warning('Payment receipt email could not be sent: '.$e->getMessage());
            }
        }

        return $payment;
    }

    public function markAsPaid(?string $paymentIntentId = null, string $paymentMethod = 'Online / Checkout'): void
    {
        $wasPaid = $this->status === 'paid';
        $remaining = (float) ($this->balance_due ?? $this->total);

        if ($remaining > 0) {
            $this->payments()->create([
                'user_id' => $this->user_id,
                'amount' => $remaining,
                'payment_method' => $paymentMethod === 'Online / Checkout' ? 'stripe' : $paymentMethod,
                'reference_number' => $paymentIntentId,
                'paid_at' => now(),
            ]);
        }

        $totalPaid = round((float) $this->payments()->sum('amount'), 2);
        if ($totalPaid == 0) {
            $totalPaid = (float) $this->total;
        }

        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'amount_paid' => $totalPaid,
            'balance_due' => 0.00,
            'stripe_payment_intent_id' => $paymentIntentId ?? $this->stripe_payment_intent_id,
        ]);

        if (! $wasPaid && $this->client?->email) {
            try {
                Mail::to($this->client->email)
                    ->send(new PaymentReceiptMail($this, $paymentMethod));
            } catch (\Throwable $e) {
                Log::warning('Payment receipt email could not be sent: '.$e->getMessage());
            }
        }
    }

    public function getPublicUrlAttribute(): string
    {
        return route('invoices.public', $this->public_token);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return Currency::symbol($this->currency ?? 'USD');
    }

    public function getFormattedTotalAttribute(): string
    {
        return Currency::format($this->total, $this->currency ?? 'USD');
    }

    public function getFormattedBalanceDueAttribute(): string
    {
        return Currency::format($this->balance_due ?? $this->total, $this->currency ?? 'USD');
    }
}
