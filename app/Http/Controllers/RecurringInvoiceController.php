<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\RecurringInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecurringInvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = $user->recurringInvoices()->with(['client', 'invoices']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQ) use ($search) {
                        $clientQ->where('name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $recurringInvoices = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => $user->recurringInvoices()->count(),
            'active' => $user->recurringInvoices()->where('status', 'active')->count(),
            'paused' => $user->recurringInvoices()->where('status', 'paused')->count(),
            'total_generated' => (int) $user->recurringInvoices()->sum('invoices_generated_count'),
        ];

        return view('recurring.index', compact('recurringInvoices', 'stats'));
    }

    public function create(): View
    {
        $user = Auth::user();
        $clients = $user->clients()->orderBy('name')->get();
        $logos = $user->logos()->latest()->get();
        $products = Product::where('user_id', $user->id)->with('category')->orderBy('name')->get();
        $categories = ProductCategory::where('user_id', $user->id)->orderBy('name')->get();

        return view('recurring.create', compact('clients', 'logos', 'products', 'categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'exists:clients,id'],
            'frequency' => ['required', 'in:weekly,biweekly,monthly,quarterly,yearly'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'due_days' => ['required', 'integer', 'min:1', 'max:180'],
            'currency' => ['required', 'string', 'max:10'],
            'style' => ['required', 'in:minimalist,corporate,creative,grid'],
            'logo_id' => ['nullable', 'exists:user_logos,id'],
            'auto_send_email' => ['nullable', 'boolean'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'additional_charges' => ['nullable', 'array'],
            'additional_charges.*.name' => ['nullable', 'string', 'max:100'],
            'additional_charges.*.type' => ['nullable', 'in:fixed,percentage'],
            'additional_charges.*.value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_instructions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['logo_id'])) {
            $user->logos()->findOrFail($validated['logo_id']);
        }
        $user->clients()->findOrFail($validated['client_id']);

        $recurring = DB::transaction(function () use ($user, $validated, $request) {
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

            $recurring = $user->recurringInvoices()->create([
                'client_id' => $validated['client_id'],
                'logo_id' => $validated['logo_id'] ?? null,
                'title' => $validated['title'],
                'frequency' => $validated['frequency'],
                'start_date' => $validated['start_date'],
                'next_issue_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'due_days' => (int) $validated['due_days'],
                'currency' => $validated['currency'],
                'style' => $validated['style'],
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
                'auto_send_email' => $request->boolean('auto_send_email'),
                'status' => 'active',
            ]);

            foreach ($itemsData as $itemData) {
                $recurring->items()->create($itemData);
            }

            return $recurring;
        });

        return redirect()->route('recurring.show', $recurring)
            ->with('success', 'Recurring invoice profile created successfully.');
    }

    public function show(RecurringInvoice $recurring): View
    {
        $this->authorizeOwner($recurring);

        $recurring->load(['client', 'items', 'logo', 'invoices.client']);

        return view('recurring.show', compact('recurring'));
    }

    public function edit(RecurringInvoice $recurring): View
    {
        $this->authorizeOwner($recurring);

        $user = Auth::user();
        $recurring->load(['client', 'items']);
        $clients = $user->clients()->orderBy('name')->get();
        $logos = $user->logos()->latest()->get();
        $products = Product::where('user_id', $user->id)->with('category')->orderBy('name')->get();
        $categories = ProductCategory::where('user_id', $user->id)->orderBy('name')->get();

        return view('recurring.edit', compact('recurring', 'clients', 'logos', 'products', 'categories'));
    }

    public function update(Request $request, RecurringInvoice $recurring): RedirectResponse
    {
        $this->authorizeOwner($recurring);
        $user = Auth::user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'client_id' => ['required', 'exists:clients,id'],
            'frequency' => ['required', 'in:weekly,biweekly,monthly,quarterly,yearly'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'due_days' => ['required', 'integer', 'min:1', 'max:180'],
            'currency' => ['required', 'string', 'max:10'],
            'style' => ['required', 'in:minimalist,corporate,creative,grid'],
            'logo_id' => ['nullable', 'exists:user_logos,id'],
            'auto_send_email' => ['nullable', 'boolean'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'additional_charges' => ['nullable', 'array'],
            'additional_charges.*.name' => ['nullable', 'string', 'max:100'],
            'additional_charges.*.type' => ['nullable', 'in:fixed,percentage'],
            'additional_charges.*.value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'payment_instructions' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        if (! empty($validated['logo_id'])) {
            $user->logos()->findOrFail($validated['logo_id']);
        }
        $user->clients()->findOrFail($validated['client_id']);

        DB::transaction(function () use ($recurring, $validated, $request) {
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

            $recurring->update([
                'client_id' => $validated['client_id'],
                'logo_id' => $validated['logo_id'] ?? null,
                'title' => $validated['title'],
                'frequency' => $validated['frequency'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'] ?? null,
                'due_days' => (int) $validated['due_days'],
                'currency' => $validated['currency'],
                'style' => $validated['style'],
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
                'auto_send_email' => $request->boolean('auto_send_email'),
            ]);

            $recurring->items()->delete();
            foreach ($itemsData as $itemData) {
                $recurring->items()->create($itemData);
            }
        });

        return redirect()->route('recurring.show', $recurring)
            ->with('success', 'Recurring profile updated successfully.');
    }

    public function destroy(RecurringInvoice $recurring): RedirectResponse
    {
        $this->authorizeOwner($recurring);

        $recurring->delete();

        return redirect()->route('recurring.index')
            ->with('success', 'Recurring invoice profile deleted successfully.');
    }

    public function toggleStatus(RecurringInvoice $recurring): RedirectResponse
    {
        $this->authorizeOwner($recurring);

        $newStatus = $recurring->status === 'active' ? 'paused' : 'active';
        $recurring->update(['status' => $newStatus]);

        $msg = $newStatus === 'active' ? 'Recurring profile activated.' : 'Recurring profile paused.';

        return back()->with('success', $msg);
    }

    public function generateNow(RecurringInvoice $recurring): RedirectResponse
    {
        $this->authorizeOwner($recurring);

        $invoice = $recurring->generateInvoice();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice #'.$invoice->invoice_number.' generated successfully from recurring schedule.');
    }

    private function authorizeOwner(RecurringInvoice $recurring): void
    {
        if ($recurring->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
