<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'transaction_date',
        'reference_number',
        'description',
        'amount',
        'type',
        'matched_invoice_id',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * Owning merchant user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Matched invoice if reconciled.
     */
    public function matchedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'matched_invoice_id');
    }

    /**
     * Reconcile this bank transaction with an invoice and record completed payment.
     */
    public function reconcileWith(Invoice $invoice): InvoicePayment
    {
        $payment = $invoice->recordPayment(
            amount: (float) $this->amount,
            paymentMethod: 'bank_transfer',
            referenceNumber: $this->reference_number ?: ('BANK-SYNC-'.$this->id),
            notes: 'Bank Statement Import Reconciled: '.$this->description,
            paidAt: $this->transaction_date ? $this->transaction_date->toDateTime() : now(),
            status: 'completed'
        );

        $this->update([
            'matched_invoice_id' => $invoice->id,
            'status' => 'matched',
        ]);

        return $payment;
    }
}
