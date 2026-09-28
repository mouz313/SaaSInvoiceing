<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\WebhookDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceApiController extends Controller
{
    /**
     * List user invoices.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Invoice::with(['client', 'items'])
            ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $term = $request->query('search');
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhereHas('client', function ($cq) use ($term) {
                        $cq->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    });
            });
        }

        $invoices = $query->latest('invoice_date')->paginate(20);

        return response()->json([
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
            ],
        ]);
    }

    /**
     * Show a single invoice.
     */
    public function show(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Not Found', 'message' => 'Invoice not found.'], 404);
        }

        $invoice->load(['client', 'items', 'payments']);

        return response()->json([
            'data' => $invoice,
        ]);
    }

    /**
     * Create a new invoice via API.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'client_name' => ['required_without:client_id', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:50'],
            'invoice_number' => ['nullable', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'template_style' => ['nullable', 'string', 'in:minimalist,corporate,creative,grid'],
            'notes' => ['nullable', 'string'],
            'tax_authority' => ['nullable', 'string'],
            'withholding_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        // Resolve or create client
        if (! empty($validated['client_id'])) {
            $client = Client::where('id', $validated['client_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        } else {
            $client = Client::firstOrCreate(
                ['user_id' => $user->id, 'email' => $validated['client_email']],
                [
                    'name' => $validated['client_name'],
                    'phone' => $validated['client_phone'] ?? null,
                    'currency' => $validated['currency'] ?? $user->default_currency ?? 'PKR',
                ]
            );
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += ($item['quantity'] * $item['unit_price']);
        }

        $whtRate = (float) ($validated['withholding_tax_rate'] ?? 0);
        $whtAmount = round(($subtotal * $whtRate) / 100, 2);
        $total = max(0, $subtotal - $whtAmount);

        $invoiceNumber = $validated['invoice_number'] ?? ('INV-'.strtoupper(Str::random(6)));

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'client_id' => $client->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'currency' => $validated['currency'] ?? $user->default_currency ?? 'PKR',
            'template_style' => $validated['template_style'] ?? 'minimalist',
            'subtotal' => $subtotal,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total' => $total,
            'balance_due' => $total,
            'amount_paid' => 0,
            'status' => 'sent',
            'notes' => $validated['notes'] ?? $user->default_notes,
            'public_token' => Str::random(32),
            'tax_authority' => $validated['tax_authority'] ?? null,
            'wht_rate' => $whtRate,
            'wht_amount' => $whtAmount,
        ]);

        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        $invoice->load(['client', 'items']);

        // Dispatch Webhook
        WebhookDispatcher::dispatch($user, 'invoice.created', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total' => (float) $invoice->total,
            'currency' => $invoice->currency,
            'client_name' => $client->name,
            'public_url' => route('invoices.public', $invoice->public_token),
        ]);

        return response()->json([
            'message' => 'Invoice created successfully.',
            'data' => $invoice,
        ], 201);
    }

    /**
     * Record a payment against an invoice via API.
     */
    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $user = $request->user();

        if ($invoice->user_id !== $user->id) {
            return response()->json(['error' => 'Not Found', 'message' => 'Invoice not found.'], 404);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$invoice->balance_due],
            'payment_method' => ['required', 'string', 'in:bank_transfer,raast,jazzcash,easypaisa,cash,stripe,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $invoice->recordPayment(
            amount: (float) $validated['amount'],
            paymentMethod: $validated['payment_method'],
            referenceNumber: $validated['reference_number'] ?? null,
            notes: $validated['notes'] ?? 'Payment recorded via Public API',
            paidAt: now(),
            status: 'completed'
        );

        $invoice->refresh();

        $event = $invoice->isPaid() ? 'invoice.paid' : 'invoice.payment_received';

        WebhookDispatcher::dispatch($user, $event, [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'payment_id' => $payment->id,
            'amount' => (float) $payment->amount,
            'payment_method' => $payment->payment_method,
            'balance_due' => (float) $invoice->balance_due,
            'status' => $invoice->status,
        ]);

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'data' => [
                'payment' => $payment,
                'invoice' => $invoice,
            ],
        ]);
    }
}
