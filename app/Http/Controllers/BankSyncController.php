<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BankSyncController extends Controller
{
    /**
     * Display bank synchronization and reconciliation hub.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = BankTransaction::with('matchedInvoice.client')
            ->where('user_id', $user->id);

        if ($request->query('filter') === 'unreconciled') {
            $query->where('status', 'unreconciled');
        } elseif ($request->query('filter') === 'matched') {
            $query->where('status', 'matched');
        }

        $transactions = $query->latest('transaction_date')->paginate(25);

        // Fetch unpaid invoices for manual match picker dropdown
        $unpaidInvoices = Invoice::with('client')
            ->where('user_id', $user->id)
            ->whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])
            ->latest('invoice_date')
            ->get();

        $stats = [
            'total_imported' => BankTransaction::where('user_id', $user->id)->count(),
            'unreconciled' => BankTransaction::where('user_id', $user->id)->where('status', 'unreconciled')->count(),
            'matched' => BankTransaction::where('user_id', $user->id)->where('status', 'matched')->count(),
            'total_credit' => BankTransaction::where('user_id', $user->id)->where('type', 'credit')->sum('amount'),
        ];

        return view('bank-sync.index', compact('transactions', 'unpaidInvoices', 'stats'));
    }

    /**
     * Upload and parse CSV bank statement (Meezan, HBL, Alfalah, or Generic).
     */
    public function upload(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'in:meezan,hbl,alfalah,generic'],
            'statement_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('statement_file');
        $handle = fopen($file->getRealPath() ?: $file->getPathname(), 'r');

        if (! $handle) {
            return back()->with('error', 'Unable to read the uploaded statement file.');
        }

        $header = null;
        $importedCount = 0;
        $matchedCount = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 2048, ',')) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // First row with headers
                if (! $header) {
                    $header = array_map(fn ($col) => strtolower(trim(str_replace([' ', '_', '-', '#'], '', $col))), $row);

                    continue;
                }

                $data = array_combine(array_slice($header, 0, count($row)), $row);

                // Extract date
                $rawDate = $data['date'] ?? $data['transdate'] ?? $data['transactiondate'] ?? $data['valuedate'] ?? now()->toDateString();
                try {
                    $parsedDate = Carbon::parse($rawDate)->toDateString();
                } catch (\Throwable) {
                    $parsedDate = now()->toDateString();
                }

                // Extract description and reference
                $desc = $data['description'] ?? $data['narration'] ?? $data['particulars'] ?? $data['details'] ?? 'Bank Transaction';
                $ref = $data['reference'] ?? $data['refno'] ?? $data['referencenumber'] ?? $data['trxid'] ?? $data['chequeno'] ?? null;

                // Extract credit / debit amounts
                $credit = floatval(preg_replace('/[^\d.]/', '', $data['credit'] ?? $data['deposit'] ?? $data['amount'] ?? 0));
                $debit = floatval(preg_replace('/[^\d.]/', '', $data['debit'] ?? $data['withdrawal'] ?? 0));

                $amount = $credit > 0 ? $credit : $debit;
                $type = $credit > 0 ? 'credit' : 'debit';

                if ($amount <= 0) {
                    continue;
                }

                // Auto-match against pending invoices
                $matchedInvoiceId = null;
                $matchStatus = 'unreconciled';

                if ($type === 'credit') {
                    // Match priority 1: reference matches invoice_number
                    $candidate = Invoice::where('user_id', $user->id)
                        ->whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])
                        ->where(function ($q) use ($ref, $desc) {
                            if (! empty($ref)) {
                                $q->where('invoice_number', 'like', "%{$ref}%");
                            }
                            $q->orWhereRaw('? LIKE CONCAT("%", invoice_number, "%")', [$desc]);
                        })
                        ->first();

                    // Match priority 2: exact balance match
                    if (! $candidate) {
                        $candidate = Invoice::where('user_id', $user->id)
                            ->whereIn('status', ['sent', 'viewed', 'overdue', 'partially_paid'])
                            ->where('balance_due', $amount)
                            ->first();
                    }

                    if ($candidate) {
                        $matchedInvoiceId = $candidate->id;
                    }
                }

                BankTransaction::create([
                    'user_id' => $user->id,
                    'bank_name' => $validated['bank_name'],
                    'transaction_date' => $parsedDate,
                    'reference_number' => $ref,
                    'description' => substr($desc, 0, 255),
                    'amount' => $amount,
                    'type' => $type,
                    'matched_invoice_id' => $matchedInvoiceId,
                    'status' => $matchStatus,
                ]);

                $importedCount++;
                if ($matchedInvoiceId) {
                    $matchedCount++;
                }
            }

            fclose($handle);
            DB::commit();
        } catch (\Throwable $e) {
            fclose($handle);
            DB::rollBack();

            return back()->with('error', 'Error parsing statement: '.$e->getMessage());
        }

        $msg = "🏦 Statement imported successfully! {$importedCount} transactions recorded.";
        if ($matchedCount > 0) {
            $msg .= " {$matchedCount} incoming payments automatically matched with pending invoices!";
        }

        return redirect()->route('bank-sync.index')->with('success', $msg);
    }

    /**
     * Settle & reconcile matched transaction with invoice.
     */
    public function reconcile(Request $request, BankTransaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $invoiceId = $request->input('invoice_id', $transaction->matched_invoice_id);

        $invoice = Invoice::where('id', $invoiceId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $transaction->reconcileWith($invoice);

        return redirect()->route('bank-sync.index')
            ->with('success', '✅ Reconciled! Payment of PKR '.number_format((float) $transaction->amount, 2)." recorded against Invoice #{$invoice->invoice_number}.");
    }

    /**
     * Ignore transaction from reconciliation list.
     */
    public function ignore(Request $request, BankTransaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $transaction->update(['status' => 'ignored']);

        return redirect()->route('bank-sync.index')
            ->with('info', 'Transaction marked as ignored.');
    }
}
