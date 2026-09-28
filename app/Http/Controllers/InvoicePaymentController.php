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
            'payment_method' => ['required', 'string', 'in:stripe,bank_transfer,raast,jazzcash,easypaisa,cash,cheque,credit_card,other'],
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

    public function approve(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        if ($invoice->user_id !== auth()->id() || $payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        $payment->update([
            'status' => 'completed',
        ]);

        $invoice->recalculatePaymentTotals($payment->payment_method);

        return back()->with('success', 'Payment of '.$invoice->currency.' '.number_format($payment->amount, 2).' verified and approved!');
    }

    public function reject(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        if ($invoice->user_id !== auth()->id() || $payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        $payment->update([
            'status' => 'rejected',
        ]);

        $invoice->recalculatePaymentTotals();

        return back()->with('info', 'Payment record marked as rejected.');
    }

    public function destroy(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        if ($invoice->user_id !== auth()->id() || $payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        $payment->delete();

        $invoice->recalculatePaymentTotals();

        return back()->with('success', 'Payment record deleted and invoice balance recalculated.');
    }
}
