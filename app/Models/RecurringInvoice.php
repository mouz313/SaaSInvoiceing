<?php

namespace App\Models;

use App\Mail\InvoiceSentMail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RecurringInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'client_id',
        'logo_id',
        'title',
        'frequency',
        'start_date',
        'next_issue_date',
        'end_date',
        'due_days',
        'currency',
        'style',
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
        'auto_send_email',
        'status',
        'last_generated_at',
        'invoices_generated_count',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'next_issue_date' => 'date',
            'end_date' => 'date',
            'last_generated_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_rate' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'additional_charges' => 'array',
            'additional_charges_total' => 'decimal:2',
            'total' => 'decimal:2',
            'auto_send_email' => 'boolean',
            'invoices_generated_count' => 'integer',
            'due_days' => 'integer',
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

    public function logo(): BelongsTo
    {
        return $this->belongsTo(UserLogo::class, 'logo_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderBy('created_at', 'desc');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'emerald',
            'paused' => 'amber',
            'completed' => 'slate',
            default => 'slate',
        };
    }

    public function calculateNextDate(?Carbon $from = null): Carbon
    {
        $base = $from ? $from->copy() : Carbon::parse($this->next_issue_date);

        return match ($this->frequency) {
            'weekly' => $base->addWeek(),
            'biweekly' => $base->addWeeks(2),
            'quarterly' => $base->addMonths(3),
            'yearly' => $base->addYear(),
            default => $base->addMonth(), // monthly
        };
    }

    public function generateInvoice(): Invoice
    {
        return DB::transaction(function () {
            $user = $this->user;

            // Generate next invoice number
            $lastInvoice = $user->invoices()->latest('id')->first();
            if ($lastInvoice && preg_match('/(\d+)$/', $lastInvoice->invoice_number, $matches)) {
                $nextNum = str_pad((int) $matches[1] + 1, 4, '0', STR_PAD_LEFT);
                $prefix = preg_replace('/\d+$/', '', $lastInvoice->invoice_number);
                $invoiceNumber = $prefix.$nextNum;
            } else {
                $count = $user->invoices()->count() + 1;
                $invoiceNumber = 'INV-'.str_pad($count, 4, '0', STR_PAD_LEFT);
            }

            $issueDate = Carbon::today();
            $dueDate = $issueDate->copy()->addDays($this->due_days ?: 14);

            $invoice = $user->invoices()->create([
                'client_id' => $this->client_id,
                'recurring_invoice_id' => $this->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $issueDate,
                'due_date' => $dueDate,
                'status' => 'sent',
                'style' => $this->style,
                'logo_id' => $this->logo_id,
                'currency' => $this->currency,
                'subtotal' => $this->subtotal,
                'tax_rate' => $this->tax_rate,
                'tax_amount' => $this->tax_amount,
                'discount_rate' => $this->discount_rate,
                'discount_amount' => $this->discount_amount,
                'additional_charges' => $this->additional_charges,
                'additional_charges_total' => $this->additional_charges_total,
                'total' => $this->total,
                'amount_paid' => 0.00,
                'balance_due' => $this->total,
                'notes' => $this->notes,
                'payment_instructions' => $this->payment_instructions,
            ]);

            foreach ($this->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                ]);
            }

            // Decrement user credits if applicable
            $user->decrementCredits();

            // Next issue date calculation
            $nextDate = $this->calculateNextDate(Carbon::parse($this->next_issue_date));
            $isCompleted = false;

            if ($this->end_date && $nextDate->greaterThan(Carbon::parse($this->end_date))) {
                $isCompleted = true;
            }

            $this->update([
                'last_generated_at' => now(),
                'invoices_generated_count' => $this->invoices_generated_count + 1,
                'next_issue_date' => $nextDate,
                'status' => $isCompleted ? 'completed' : $this->status,
            ]);

            // Auto-send email if configured and client has email
            if ($this->auto_send_email && $this->client?->email) {
                try {
                    $invoice->load(['client', 'items', 'user', 'logo']);
                    Mail::to($this->client->email)->send(new InvoiceSentMail(
                        $invoice,
                        '',
                        true
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Recurring invoice auto-email failed: '.$e->getMessage());
                }
            }

            return $invoice;
        });
    }
}
