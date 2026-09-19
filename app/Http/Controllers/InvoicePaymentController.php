<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoicePaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $balance = (float) ($invoice->balance_due ?? $invoice->total);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.($balance > 0 ? $balance : $invoice->total)],
            'payment_method' => ['required', 'string', 'in:stripe,bank_transfer,cash,cheque,credit_card,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoice->recordPayment(
            amount: (float) $validated['amount'],
            paymentMethod: $validated['payment_method'],
            referenceNumber: $validated['reference_number'] ?? null,
            notes: $validated['notes'] ?? null,
            paidAt: $validated['paid_at']
        );

        return back()->with('success', 'Payment of '.$invoice->currency.' '.number_format($validated['amount'], 2).' recorded successfully.');
    }

    public function destroy(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        if ($invoice->user_id !== auth()->id() || $payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        $payment->delete();

        $newPaid = round((float) $invoice->payments()->sum('amount'), 2);
        $newBal = max(0, round((float) $invoice->total - $newPaid, 2));

        $newStatus = $invoice->status;
        if ($newBal <= 0) {
            $newStatus = 'paid';
        } elseif ($newPaid > 0) {
            $newStatus = 'partially_paid';
        } elseif ($invoice->status === 'partially_paid') {
            $newStatus = 'sent';
        }

        $invoice->update([
            'amount_paid' => $newPaid,
            'balance_due' => $newBal,
            'status' => $newStatus,
            'paid_at' => $newBal <= 0 ? $invoice->paid_at : null,
        ]);

        return back()->with('success', 'Payment record deleted and invoice balance recalculated.');
    }
}
