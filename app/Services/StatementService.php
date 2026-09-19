<?php

namespace App\Services;

use App\Models\Client;
use Carbon\Carbon;

class StatementService
{
    /**
     * Generate an account statement for a given client within a date range.
     *
     * @return array{
     *     client: Client,
     *     user: mixed,
     *     startDate: ?Carbon,
     *     endDate: ?Carbon,
     *     openingBalance: float,
     *     ledger: array<int, array{
     *         date: Carbon,
     *         type: string,
     *         reference: string,
     *         description: string,
     *         debit: float,
     *         credit: float,
     *         balance: float,
     *         model: mixed
     *     }>,
     *     totalInvoiced: float,
     *     totalPaid: float,
     *     closingBalance: float,
     *     currency: string
     * }
     */
    public function generate(Client $client, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $openingBalance = 0.00;

        if ($startDate) {
            $priorInvoiced = (float) $client->invoices()
                ->where('invoice_date', '<', $startDate->copy()->startOfDay())
                ->whereIn('status', ['sent', 'partially_paid', 'paid', 'overdue'])
                ->sum('total');

            $priorPaid = (float) $client->invoicePayments()
                ->where('invoice_payments.paid_at', '<', $startDate->copy()->startOfDay())
                ->sum('amount');

            $openingBalance = round($priorInvoiced - $priorPaid, 2);
        }

        // Fetch Invoices in period
        $invoicesQuery = $client->invoices()
            ->whereIn('status', ['sent', 'partially_paid', 'paid', 'overdue']);

        if ($startDate) {
            $invoicesQuery->where('invoice_date', '>=', $startDate->copy()->startOfDay());
        }
        if ($endDate) {
            $invoicesQuery->where('invoice_date', '<=', $endDate->copy()->endOfDay());
        }

        $invoices = $invoicesQuery->get();

        // Fetch Payments in period
        $paymentsQuery = $client->invoicePayments()->with('invoice');

        if ($startDate) {
            $paymentsQuery->where('invoice_payments.paid_at', '>=', $startDate->copy()->startOfDay());
        }
        if ($endDate) {
            $paymentsQuery->where('invoice_payments.paid_at', '<=', $endDate->copy()->endOfDay());
        }

        $payments = $paymentsQuery->get();

        // Assemble Ledger items
        $items = [];

        foreach ($invoices as $invoice) {
            $items[] = [
                'date' => Carbon::parse($invoice->invoice_date),
                'type' => 'invoice',
                'reference' => 'Invoice #'.$invoice->invoice_number,
                'description' => 'Invoice issued (Due: '.($invoice->due_date ? Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A').')',
                'debit' => (float) $invoice->total,
                'credit' => 0.00,
                'model' => $invoice,
            ];
        }

        foreach ($payments as $payment) {
            $method = ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Payment'));
            $invRef = $payment->invoice ? ' for #'.$payment->invoice->invoice_number : '';
            $items[] = [
                'date' => Carbon::parse($payment->paid_at),
                'type' => 'payment',
                'reference' => 'Payment'.($payment->reference_number ? ' ('.$payment->reference_number.')' : ''),
                'description' => "{$method} received{$invRef}",
                'debit' => 0.00,
                'credit' => (float) $payment->amount,
                'model' => $payment,
            ];
        }

        // Sort chronologically
        usort($items, function ($a, $b) {
            return $a['date']->timestamp <=> $b['date']->timestamp;
        });

        // Compute running balance
        $currentBalance = $openingBalance;
        $ledger = [];
        $totalInvoiced = 0.00;
        $totalPaid = 0.00;

        foreach ($items as $item) {
            $currentBalance += ($item['debit'] - $item['credit']);
            $item['balance'] = round($currentBalance, 2);
            $totalInvoiced += $item['debit'];
            $totalPaid += $item['credit'];
            $ledger[] = $item;
        }

        $closingBalance = round($openingBalance + $totalInvoiced - $totalPaid, 2);

        return [
            'client' => $client,
            'user' => $client->user,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
            'ledger' => $ledger,
            'totalInvoiced' => round($totalInvoiced, 2),
            'totalPaid' => round($totalPaid, 2),
            'closingBalance' => $closingBalance,
            'currency' => $client->currency ?: ($invoices->first()?->currency ?? 'USD'),
        ];
    }
}
