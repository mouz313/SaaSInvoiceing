<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceSentMail;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $invoices = Auth::user()->invoices()
            ->with('client')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('company_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('invoices.index', compact('invoices', 'search', 'status'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->hasCredits()) {
            return redirect()->route('dashboard')
                ->with('error', 'You have exhausted your invoice credits. Please upgrade your package to create more invoices.');
        }

        $clients = $user->clients()->orderBy('name')->get();
        $logos = $user->logos()->latest()->get();
        $products = $user->products()->with('category')->latest()->get();
        $categories = $user->productCategories()->get();

        // Auto-generate invoice number
        $latestInvoice = $user->invoices()->latest('id')->first();
        $nextId = $latestInvoice ? ($latestInvoice->id + 1) : 1;
        $defaultNumber = 'INV-'.date('Y').'-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

        $templates = InvoiceTemplate::where('is_active', true)->orderBy('sort_order')->get();
        $ownedSlugs = $user->ownedTemplateSlugs();
        $requestedStyle = $request->query('style');
        $defaultStyle = ($requestedStyle && in_array($requestedStyle, $ownedSlugs, true)) ? $requestedStyle : 'minimalist';
        $mockInvoice = InvoiceTemplate::sampleInvoice($user);

        return view('invoices.create', compact('clients', 'defaultNumber', 'logos', 'products', 'categories', 'templates', 'ownedSlugs', 'defaultStyle', 'mockInvoice'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->hasCredits()) {
            return redirect()->route('dashboard')
                ->with('error', 'You have exhausted your invoice credits. Please upgrade your package.');
        }

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'invoice_number' => ['required', 'string', 'max:50'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'status' => ['required', 'in:draft,sent,paid,overdue'],
            'style' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (! $user->hasTemplateAccess($value)) {
                        $fail("You do not have access to the '{$value}' template. Please unlock it from the Template Store first.");
                    }
                },
            ],
            'logo_id' => ['nullable', 'exists:user_logos,id'],
            'currency' => ['required', 'string', 'max:10'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'additional_charges' => ['nullable', 'array'],
            'additional_charges.*.name' => ['nullable', 'string', 'max:100'],
            'additional_charges.*.type' => ['nullable', 'in:fixed,percentage'],
            'additional_charges.*.value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_instructions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        // Ensure logo belongs to user
        if (! empty($validated['logo_id'])) {
            $user->logos()->findOrFail($validated['logo_id']);
        }

        // Check client belongs to user
        $client = $user->clients()->findOrFail($validated['client_id']);

        $invoice = DB::transaction(function () use ($user, $validated) {
            $taxRate = (float) ($validated['tax_rate'] ?? 0);
            $discountRate = (float) ($validated['discount_rate'] ?? 0);

            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $lineTotal = round($qty * $unitPrice, 2);
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'amount' => $lineTotal,
                ];
            }

            $discountAmount = round($subtotal * ($discountRate / 100), 2);
            $taxable = $subtotal - $discountAmount;
            $taxAmount = round($taxable * ($taxRate / 100), 2);

            $additionalCharges = [];
            $additionalChargesTotal = 0;
            if (! empty($validated['additional_charges'])) {
                foreach ($validated['additional_charges'] as $charge) {
                    $name = trim($charge['name'] ?? '');
                    if ($name === '') {
                        continue;
                    }
                    $val = (float) ($charge['value'] ?? 0);
                    $type = ($charge['type'] ?? 'fixed') === 'percentage' ? 'percentage' : 'fixed';
                    $amount = $type === 'percentage' ? round($taxable * ($val / 100), 2) : round($val, 2);
                    $additionalChargesTotal += $amount;

                    $additionalCharges[] = [
                        'name' => $name,
                        'type' => $type,
                        'value' => $val,
                        'amount' => $amount,
                    ];
                }
            }

            $total = $taxable + $taxAmount + $additionalChargesTotal;

            $invoice = $user->invoices()->create([
                'client_id' => $validated['client_id'],
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'style' => $validated['style'],
                'logo_id' => $validated['logo_id'] ?? null,
                'currency' => $validated['currency'],
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_rate' => $discountRate,
                'discount_amount' => $discountAmount,
                'additional_charges' => ! empty($additionalCharges) ? $additionalCharges : null,
                'additional_charges_total' => $additionalChargesTotal,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'payment_instructions' => $validated['payment_instructions'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }

            $user->decrementCredits();

            return $invoice;
        });

        if ($request->boolean('send_email_now') && $client->email) {
            try {
                $invoice->load(['client', 'items', 'user', 'logo']);
                Mail::to($client->email)->send(new InvoiceSentMail(
                    $invoice,
                    '',
                    true
                ));
                if ($invoice->status === 'draft') {
                    $invoice->update(['status' => 'sent']);
                }
            } catch (\Throwable $e) {
                Log::warning('Direct invoice email could not be sent: '.$e->getMessage());
            }
        }

        if ($request->filled('time_entry_ids')) {
            Auth::user()->timeEntries()
                ->whereIn('id', (array) $request->input('time_entry_ids'))
                ->update([
                    'is_billed' => true,
                    'invoice_id' => $invoice->id,
                ]);
        }

        if ($request->filled('expense_ids')) {
            Auth::user()->expenses()
                ->whereIn('id', (array) $request->input('expense_ids'))
                ->update([
                    'is_billed' => true,
                    'invoice_id' => $invoice->id,
                ]);
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully!');
    }

    public function show(Invoice $invoice): View
    {
        $this->authorizeInvoice($invoice);

        $invoice->load(['client', 'items', 'user', 'logo']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice): View
    {
        $this->authorizeInvoice($invoice);

        $invoice->load(['client', 'items']);
        $clients = Auth::user()->clients()->orderBy('name')->get();
        $logos = Auth::user()->logos()->latest()->get();
        $products = Auth::user()->products()->with('category')->latest()->get();
        $categories = Auth::user()->productCategories()->get();
        $templates = InvoiceTemplate::where('is_active', true)->orderBy('sort_order')->get();
        $ownedSlugs = Auth::user()->ownedTemplateSlugs();
        $mockInvoice = $invoice;

        return view('invoices.edit', compact('invoice', 'clients', 'logos', 'products', 'categories', 'templates', 'ownedSlugs', 'mockInvoice'));
    }

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        $user = Auth::user();

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'invoice_number' => ['required', 'string', 'max:50'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:invoice_date'],
            'status' => ['required', 'in:draft,sent,paid,overdue'],
            'style' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user, $invoice) {
                    // Allow keeping existing style if already on invoice or if user owns it
                    if ($value !== $invoice->style && ! $user->hasTemplateAccess($value)) {
                        $fail("You do not have access to the '{$value}' template. Please unlock it from the Template Store first.");
                    }
                },
            ],
            'logo_id' => ['nullable', 'exists:user_logos,id'],
            'currency' => ['required', 'string', 'max:10'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'additional_charges' => ['nullable', 'array'],
            'additional_charges.*.name' => ['nullable', 'string', 'max:100'],
            'additional_charges.*.type' => ['nullable', 'in:fixed,percentage'],
            'additional_charges.*.value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_instructions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $user = Auth::user();
        $user->clients()->findOrFail($validated['client_id']);

        // Ensure logo belongs to user
        if (! empty($validated['logo_id'])) {
            $user->logos()->findOrFail($validated['logo_id']);
        }

        DB::transaction(function () use ($invoice, $validated) {
            $taxRate = (float) ($validated['tax_rate'] ?? 0);
            $discountRate = (float) ($validated['discount_rate'] ?? 0);

            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $lineTotal = round($qty * $unitPrice, 2);
                $subtotal += $lineTotal;

                $itemsData[] = [
                    'description' => $item['description'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'amount' => $lineTotal,
                ];
            }

            $discountAmount = round($subtotal * ($discountRate / 100), 2);
            $taxable = $subtotal - $discountAmount;
            $taxAmount = round($taxable * ($taxRate / 100), 2);

            $additionalCharges = [];
            $additionalChargesTotal = 0;
            if (! empty($validated['additional_charges'])) {
                foreach ($validated['additional_charges'] as $charge) {
                    $name = trim($charge['name'] ?? '');
                    if ($name === '') {
                        continue;
                    }
                    $val = (float) ($charge['value'] ?? 0);
                    $type = ($charge['type'] ?? 'fixed') === 'percentage' ? 'percentage' : 'fixed';
                    $amount = $type === 'percentage' ? round($taxable * ($val / 100), 2) : round($val, 2);
                    $additionalChargesTotal += $amount;

                    $additionalCharges[] = [
                        'name' => $name,
                        'type' => $type,
                        'value' => $val,
                        'amount' => $amount,
                    ];
                }
            }

            $total = $taxable + $taxAmount + $additionalChargesTotal;

            $invoice->update([
                'client_id' => $validated['client_id'],
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'style' => $validated['style'],
                'logo_id' => $validated['logo_id'] ?? null,
                'currency' => $validated['currency'],
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_rate' => $discountRate,
                'discount_amount' => $discountAmount,
                'additional_charges' => ! empty($additionalCharges) ? $additionalCharges : null,
                'additional_charges_total' => $additionalChargesTotal,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'payment_instructions' => $validated['payment_instructions'] ?? null,
            ]);

            $invoice->items()->delete();
            foreach ($itemsData as $itemData) {
                $invoice->items()->create($itemData);
            }
        });

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    public function updateStatus(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);

        $validated = $request->validate([
            'status' => ['required', 'in:draft,sent,paid,overdue'],
        ]);

        if ($validated['status'] === 'paid' && ! $invoice->isPaid()) {
            $invoice->markAsPaid(null, 'Manual / Recorded by Business');
        } else {
            $invoice->update(['status' => $validated['status']]);
        }

        return back()->with('success', 'Invoice status updated to '.strtoupper($validated['status']));
    }

    public function updateStyle(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        $user = Auth::user();

        $validated = $request->validate([
            'style' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (! $user->hasTemplateAccess($value)) {
                        $fail("You do not have access to the '{$value}' template. Please unlock it from the Template Store first.");
                    }
                },
            ],
        ]);

        $invoice->update(['style' => $validated['style']]);

        return back()->with('success', 'Invoice template changed to '.ucfirst($validated['style']));
    }

    public function sendEmail(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);

        $validated = $request->validate([
            'recipient_email' => ['required', 'email'],
            'custom_message' => ['nullable', 'string', 'max:2000'],
            'attach_pdf' => ['nullable', 'boolean'],
        ]);

        $invoice->load(['client', 'items', 'user', 'logo']);

        try {
            Mail::to($validated['recipient_email'])
                ->send(new InvoiceSentMail(
                    $invoice,
                    $validated['custom_message'] ?? '',
                    $request->boolean('attach_pdf', true)
                ));

            // Automatically move from draft to sent if unpaid
            if ($invoice->status === 'draft') {
                $invoice->update(['status' => 'sent']);
            }

            return back()->with('success', "Invoice #{$invoice->invoice_number} successfully emailed to {$validated['recipient_email']}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to send email: '.$e->getMessage());
        }
    }

    public function downloadPdf(Invoice $invoice): Response
    {
        $this->authorizeInvoice($invoice);

        $invoice->load(['client', 'items', 'user', 'logo']);

        $viewName = view()->exists("invoices.templates.{$invoice->style}")
            ? "invoices.templates.{$invoice->style}"
            : 'invoices.templates.minimalist';

        $pdf = Pdf::loadView($viewName, [
            'invoice' => $invoice,
            'isPdf' => true,
        ]);

        $fileName = "{$invoice->invoice_number}.pdf";

        return $pdf->download($fileName);
    }

    private function authorizeInvoice(Invoice $invoice): void
    {
        if ($invoice->user_id !== Auth::id() && ! Auth::user()?->isAdmin()) {
            abort(403, 'Unauthorized access to this invoice.');
        }
    }
}
