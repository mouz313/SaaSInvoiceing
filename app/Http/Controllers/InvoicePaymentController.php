<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoicePaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizePaymentAction($invoice);

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
        $this->authorizePaymentAction($invoice, $payment);

        $payment->update([
            'status' => 'completed',
        ]);

        $invoice->recalculatePaymentTotals($payment->payment_method);

        return back()->with('success', 'Payment of '.$invoice->currency.' '.number_format($payment->amount, 2).' verified and approved!');
    }

    public function reject(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        $this->authorizePaymentAction($invoice, $payment);

        $payment->update([
            'status' => 'rejected',
        ]);

        $invoice->recalculatePaymentTotals();

        return back()->with('info', 'Payment record marked as rejected.');
    }

    public function destroy(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        $this->authorizePaymentAction($invoice, $payment);

        $payment->delete();

        $invoice->recalculatePaymentTotals();

        return back()->with('success', 'Payment record deleted and invoice balance recalculated.');
    }

    private function authorizePaymentAction(Invoice $invoice, ?InvoicePayment $payment = null): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        if ($user->isAdmin()) {
            return;
        }

        if ($payment && $payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        if ($invoice->user_id === $user->id) {
            return;
        }

        $role = $user->roleInAccount($invoice->user);

        if (! in_array($role, ['owner', 'admin', 'accountant'], true)) {
            abort(403, 'Your role does not have permission to manage invoice payments.');
        }
    }

    /**
     * Authenticated download of payment proof receipt.
     */
    public function downloadProof(Invoice $invoice, InvoicePayment $payment)
    {
        $user = auth()->user();

        $isOwner = ($invoice->user_id === $user->id);
        $isTeam = $user->roleInAccount($invoice->user) !== 'none';
        $isAdmin = $user->isAdmin();

        if (! ($isOwner || $isTeam || $isAdmin) || $payment->invoice_id !== $invoice->id) {
            abort(403);
        }

        if (empty($payment->proof_file)) {
            abort(404, 'No proof file attached to this payment.');
        }

        // Support private disk and fallback to public disk for legacy records
        if (Storage::disk('local')->exists($payment->proof_file)) {
            return Storage::disk('local')->response($payment->proof_file);
        }

        if (Storage::disk('public')->exists($payment->proof_file)) {
            return Storage::disk('public')->response($payment->proof_file);
        }

        abort(404, 'File not found on storage.');
    }
}
