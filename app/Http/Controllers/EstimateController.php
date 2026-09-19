<?php

namespace App\Http\Controllers;

use App\Mail\EstimateSentMail;
use App\Models\Estimate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EstimateController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $estimates = Auth::user()->estimates()
            ->with(['client', 'convertedInvoice'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('estimate_number', 'like', "%{$search}%")
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

        $stats = [
            'total' => Auth::user()->estimates()->count(),
            'pending' => Auth::user()->estimates()->whereIn('status', ['draft', 'sent'])->count(),
            'accepted' => Auth::user()->estimates()->where('status', 'accepted')->count(),
            'invoiced' => Auth::user()->estimates()->where('status', 'invoiced')->count(),
            'total_amount' => Auth::user()->estimates()->sum('total'),
        ];

        return view('estimates.index', compact('estimates', 'search', 'status', 'stats'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $clients = $user->clients()->orderBy('name')->get();
        $logos = $user->logos()->latest()->get();
        $products = $user->products()->with('category')->latest()->get();
        $categories = $user->productCategories()->get();

        $latest = $user->estimates()->latest('id')->first();
        $nextId = $latest ? ($latest->id + 1) : 1;
        $defaultNumber = 'EST-'.date('Y').'-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

        return view('estimates.create', compact('clients', 'defaultNumber', 'logos', 'products', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'estimate_number' => ['required', 'string', 'max:50'],
            'estimate_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:estimate_date'],
            'status' => ['required', 'in:draft,sent,accepted,declined,invoiced'],
            'style' => ['required', 'in:minimalist,corporate,creative,grid'],
            'logo_id' => ['nullable', 'exists:user_logos,id'],
            'currency' => ['required', 'string', 'max:10'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'additional_charges' => ['nullable', 'array'],
            'additional_charges.*.name' => ['nullable', 'string', 'max:100'],
            'additional_charges.*.type' => ['nullable', 'in:fixed,percentage'],
            'additional_charges.*.value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['logo_id'])) {
            $user->logos()->findOrFail($validated['logo_id']);
        }
        $client = $user->clients()->findOrFail($validated['client_id']);

        $estimate = DB::transaction(function () use ($user, $validated) {
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

            $estimate = $user->estimates()->create([
                'client_id' => $validated['client_id'],
                'estimate_number' => $validated['estimate_number'],
                'estimate_date' => $validated['estimate_date'],
                'expiry_date' => $validated['expiry_date'],
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
                'terms' => $validated['terms'] ?? null,
            ]);

            foreach ($itemsData as $itemData) {
                $estimate->items()->create($itemData);
            }

            return $estimate;
        });

        if ($request->boolean('send_email_now') && $client->email) {
            try {
                $estimate->load(['client', 'items', 'user', 'logo']);
                Mail::to($client->email)->send(new EstimateSentMail($estimate, '', true));
                if ($estimate->status === 'draft') {
                    $estimate->update(['status' => 'sent']);
                }
            } catch (\Throwable $e) {
                Log::warning('Direct estimate email could not be sent: '.$e->getMessage());
            }
        }

        return redirect()->route('estimates.index')->with('success', 'Quotation / Estimate created successfully!');
    }

    public function show(Estimate $estimate): View
    {
        $this->authorizeEstimate($estimate);
        $estimate->load(['client', 'items', 'user', 'logo', 'convertedInvoice']);

        return view('estimates.show', compact('estimate'));
    }

    public function edit(Estimate $estimate): View
    {
        $this->authorizeEstimate($estimate);
        $user = Auth::user();

        $clients = $user->clients()->orderBy('name')->get();
        $logos = $user->logos()->latest()->get();
        $products = $user->products()->with('category')->latest()->get();
        $categories = $user->productCategories()->get();
        $estimate->load(['items']);

        return view('estimates.edit', compact('estimate', 'clients', 'logos', 'products', 'categories'));
    }

    public function update(Request $request, Estimate $estimate): RedirectResponse
    {
        $this->authorizeEstimate($estimate);
        $user = Auth::user();

        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'estimate_number' => ['required', 'string', 'max:50'],
            'estimate_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:estimate_date'],
            'status' => ['required', 'in:draft,sent,accepted,declined,invoiced'],
            'style' => ['required', 'in:minimalist,corporate,creative,grid'],
            'logo_id' => ['nullable', 'exists:user_logos,id'],
            'currency' => ['required', 'string', 'max:10'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'additional_charges' => ['nullable', 'array'],
            'additional_charges.*.name' => ['nullable', 'string', 'max:100'],
            'additional_charges.*.type' => ['nullable', 'in:fixed,percentage'],
            'additional_charges.*.value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['logo_id'])) {
            $user->logos()->findOrFail($validated['logo_id']);
        }
        $user->clients()->findOrFail($validated['client_id']);

        DB::transaction(function () use ($estimate, $validated) {
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

            $estimate->update([
                'client_id' => $validated['client_id'],
                'estimate_number' => $validated['estimate_number'],
                'estimate_date' => $validated['estimate_date'],
                'expiry_date' => $validated['expiry_date'],
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
                'terms' => $validated['terms'] ?? null,
            ]);

            $estimate->items()->delete();
            foreach ($itemsData as $itemData) {
                $estimate->items()->create($itemData);
            }
        });

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate updated successfully!');
    }

    public function destroy(Estimate $estimate): RedirectResponse
    {
        $this->authorizeEstimate($estimate);
        $estimate->delete();

        return redirect()->route('estimates.index')->with('success', 'Estimate deleted successfully.');
    }

    public function updateStatus(Request $request, Estimate $estimate): RedirectResponse
    {
        $this->authorizeEstimate($estimate);

        $validated = $request->validate([
            'status' => ['required', 'in:draft,sent,accepted,declined,invoiced'],
        ]);

        $estimate->update(['status' => $validated['status']]);

        return back()->with('success', 'Estimate status updated to '.strtoupper($validated['status']));
    }

    public function convert(Request $request, Estimate $estimate): RedirectResponse
    {
        $this->authorizeEstimate($estimate);
        $user = Auth::user();

        if ($estimate->isInvoiced()) {
            return redirect()->route('invoices.show', $estimate->converted_invoice_id)
                ->with('info', 'This estimate has already been converted to an invoice.');
        }

        if (! $user->hasCredits()) {
            return back()->with('error', 'You need at least 1 invoice credit to convert this estimate to an invoice.');
        }

        $estimate->loadMissing(['items', 'client']);

        $invoice = DB::transaction(function () use ($user, $estimate) {
            $latestInvoice = $user->invoices()->latest('id')->first();
            $nextId = $latestInvoice ? ($latestInvoice->id + 1) : 1;
            $invoiceNumber = 'INV-'.date('Y').'-'.str_pad((string) $nextId, 4, '0', STR_PAD_LEFT);

            $invoice = $user->invoices()->create([
                'client_id' => $estimate->client_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(14)->toDateString(),
                'status' => 'draft',
                'style' => $estimate->style,
                'logo_id' => $estimate->logo_id,
                'currency' => $estimate->currency,
                'subtotal' => $estimate->subtotal,
                'tax_rate' => $estimate->tax_rate,
                'tax_amount' => $estimate->tax_amount,
                'discount_rate' => $estimate->discount_rate,
                'discount_amount' => $estimate->discount_amount,
                'additional_charges' => $estimate->additional_charges,
                'additional_charges_total' => $estimate->additional_charges_total,
                'total' => $estimate->total,
                'notes' => $estimate->notes,
                'payment_instructions' => $user->default_payment_instructions,
            ]);

            foreach ($estimate->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                ]);
            }

            $estimate->update([
                'status' => 'invoiced',
                'converted_invoice_id' => $invoice->id,
            ]);

            $user->decrementCredits();

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Estimate #{$estimate->estimate_number} converted into Invoice #{$invoice->invoice_number} successfully!");
    }

    public function sendEmail(Request $request, Estimate $estimate): RedirectResponse
    {
        $this->authorizeEstimate($estimate);

        $validated = $request->validate([
            'recipient_email' => ['required', 'email'],
            'custom_message' => ['nullable', 'string', 'max:2000'],
            'attach_pdf' => ['nullable', 'boolean'],
        ]);

        $estimate->load(['client', 'items', 'user', 'logo']);

        try {
            Mail::to($validated['recipient_email'])
                ->send(new EstimateSentMail(
                    $estimate,
                    $validated['custom_message'] ?? '',
                    $request->boolean('attach_pdf', true)
                ));

            if ($estimate->status === 'draft') {
                $estimate->update(['status' => 'sent']);
            }

            return back()->with('success', "Estimate #{$estimate->estimate_number} successfully emailed to {$validated['recipient_email']}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to send email: '.$e->getMessage());
        }
    }

    public function downloadPdf(Estimate $estimate): Response
    {
        $this->authorizeEstimate($estimate);
        $estimate->load(['client', 'items', 'user', 'logo']);

        $viewName = match ($estimate->style) {
            'corporate' => 'estimates.templates.corporate',
            'creative' => 'estimates.templates.creative',
            'grid' => 'estimates.templates.grid',
            default => 'estimates.templates.minimalist',
        };

        $pdf = Pdf::loadView($viewName, [
            'estimate' => $estimate,
            'isPdf' => true,
        ]);

        return $pdf->download("{$estimate->estimate_number}.pdf");
    }

    public function publicView(string $token): View
    {
        $estimate = Estimate::with(['client', 'items', 'user', 'logo', 'convertedInvoice'])
            ->where('public_token', $token)
            ->firstOrFail();

        $estimate->markAsViewed();

        return view('estimates.public', compact('estimate'));
    }

    public function publicAccept(Request $request, string $token): RedirectResponse
    {
        $estimate = Estimate::where('public_token', $token)->firstOrFail();

        if ($estimate->status !== 'invoiced') {
            $estimate->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
        }

        return back()->with('success', 'Proposal accepted successfully! The vendor has been notified.');
    }

    public function publicDecline(Request $request, string $token): RedirectResponse
    {
        $estimate = Estimate::where('public_token', $token)->firstOrFail();

        $estimate->update([
            'status' => 'declined',
            'declined_at' => now(),
            'decline_reason' => $request->input('reason'),
        ]);

        return back()->with('info', 'Proposal marked as declined.');
    }

    private function authorizeEstimate(Estimate $estimate): void
    {
        if ($estimate->user_id !== Auth::id() && ! Auth::user()?->isAdmin()) {
            abort(403, 'Unauthorized access to this estimate.');
        }
    }
}
