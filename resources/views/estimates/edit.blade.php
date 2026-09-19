@extends('layouts.app')

@section('title', 'Edit Quotation #' . $estimate->estimate_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-8" 
     x-data="{
        taxRate: {{ (float) $estimate->tax_rate }},
        discountRate: {{ (float) $estimate->discount_rate }},
        currency: '{{ old('currency', $estimate->currency) }}',
        selectedStyle: '{{ $estimate->style }}',
        selectedLogoId: {{ $estimate->logo_id ?: 'null' }},
        logos: {{ json_encode($logos->map(fn($l) => ['id' => $l->id, 'filename' => $l->filename, 'url' => $l->url])) }},
        products: {{ json_encode($products->map(fn($p) => [
            'id' => $p->id,
            'category_id' => $p->category_id,
            'category_name' => $p->category?->name ?? 'Uncategorized',
            'name' => $p->name,
            'description' => $p->description ?? '',
            'price' => (float)$p->price,
            'unit' => $p->unit ?? 'item'
        ])) }},
        selectedCatalogCategory: 'all',
        catalogSearch: '',
        get filteredProducts() {
            return this.products.filter(p => {
                const matchCat = this.selectedCatalogCategory === 'all' || String(p.category_id) === String(this.selectedCatalogCategory);
                const matchSearch = !this.catalogSearch || p.name.toLowerCase().includes(this.catalogSearch.toLowerCase()) || p.description.toLowerCase().includes(this.catalogSearch.toLowerCase());
                return matchCat && matchSearch;
            });
        },
        addProductToEstimate(p) {
            this.items.push({
                description: p.name + (p.description ? ' — ' + p.description : ''),
                quantity: 1,
                unit_price: parseFloat(p.price) || 0
            });
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },
        items: {{ json_encode($estimate->items->map(fn($i) => [
            'description' => $i->description,
            'quantity' => (float)$i->quantity,
            'unit_price' => (float)$i->unit_price
        ])) }},
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0.00 });
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        additionalCharges: {{ json_encode($estimate->additional_charges ?? []) }},
        addAdditionalCharge() {
            this.additionalCharges.push({ name: '', type: 'fixed', value: 0 });
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },
        removeAdditionalCharge(index) {
            this.additionalCharges.splice(index, 1);
        },
        get subtotal() {
            return this.items.reduce((acc, item) => acc + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)), 0);
        },
        get discountAmount() {
            return this.subtotal * ((parseFloat(this.discountRate) || 0) / 100);
        },
        get taxableAmount() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },
        get taxAmount() {
            return this.taxableAmount * ((parseFloat(this.taxRate) || 0) / 100);
        },
        get additionalChargesTotal() {
            return this.additionalCharges.reduce((acc, charge) => {
                const val = parseFloat(charge.value) || 0;
                if (charge.type === 'percentage') {
                    return acc + (this.taxableAmount * (val / 100));
                }
                return acc + val;
            }, 0);
        },
        get grandTotal() {
            return this.taxableAmount + this.taxAmount + this.additionalChargesTotal;
        }
     }">

    <!-- Page Header -->
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
        <div>
            <a href="{{ route('estimates.show', $estimate) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Quote #{{ $estimate->estimate_number }}
            </a>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Edit Quotation Proposal</h1>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-200 text-sm">
            <p class="font-bold mb-1 flex items-center gap-2"><i data-lucide="alert-circle" class="w-4 h-4"></i> Please correct the following errors:</p>
            <ul class="list-disc list-inside space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('estimates.update', $estimate) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')
        <input type="hidden" name="style" :value="selectedStyle">
        <input type="hidden" name="logo_id" :value="selectedLogoId">

        <!-- Top Grid: Metadata Card -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-6">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="file-check" class="w-5 h-5 text-indigo-600"></i> Proposal Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Client Selection -->
                <div>
                    <label for="client_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Client *</label>
                    <select name="client_id" id="client_id" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $estimate->client_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} {{ $client->company_name ? "({$client->company_name})" : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Estimate Number -->
                <div>
                    <label for="estimate_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Quote Number *</label>
                    <input type="text" name="estimate_number" id="estimate_number" required value="{{ old('estimate_number', $estimate->estimate_number) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                </div>

                <!-- Currency -->
                <div>
                    <label for="currency" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Currency *</label>
                    <select name="currency" id="currency" x-model="currency" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="PKR">PKR (Rs)</option>
                        <option value="CAD">CAD ($)</option>
                        <option value="AUD">AUD ($)</option>
                        <option value="AED">AED</option>
                    </select>
                </div>

                <!-- Quote Date -->
                <div>
                    <label for="estimate_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Quote Date *</label>
                    <input type="date" name="estimate_date" id="estimate_date" required value="{{ old('estimate_date', $estimate->estimate_date->format('Y-m-d')) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                </div>

                <!-- Expiry Date -->
                <div>
                    <label for="expiry_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Valid Until *</label>
                    <input type="date" name="expiry_date" id="expiry_date" required value="{{ old('expiry_date', $estimate->expiry_date->format('Y-m-d')) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Status</label>
                    <select name="status" id="status" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                        <option value="draft" {{ old('status', $estimate->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ old('status', $estimate->status) === 'sent' ? 'selected' : '' }}>Sent / Issued</option>
                        <option value="accepted" {{ old('status', $estimate->status) === 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="declined" {{ old('status', $estimate->status) === 'declined' ? 'selected' : '' }}>Declined</option>
                        <option value="invoiced" {{ old('status', $estimate->status) === 'invoiced' ? 'selected' : '' }}>Invoiced</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ⚡ Quick Add Products -->
        @if($products->isNotEmpty())
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="zap" class="w-5 h-5 text-amber-500"></i>
                            1-Click Products &amp; Services
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Add deliverables straight into the proposal lines.</p>
                    </div>
                    <input type="text" x-model="catalogSearch" placeholder="Filter items..."
                           class="px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-xs text-slate-900 dark:text-white w-full sm:w-48 focus:outline-none focus:ring-2 focus:ring-indigo-600">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 max-h-56 overflow-y-auto pr-1">
                    <template x-for="prod in filteredProducts" :key="prod.id">
                        <button type="button" @click="addProductToEstimate(prod)"
                                class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/60 hover:border-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 transition text-left group">
                            <div class="flex items-start justify-between gap-1">
                                <span class="font-bold text-xs text-slate-800 dark:text-slate-200 line-clamp-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400" x-text="prod.name"></span>
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-600 shrink-0 mt-0.5"></i>
                            </div>
                            <div class="flex items-center justify-between mt-1 text-[11px]">
                                <span class="text-slate-400 dark:text-slate-500" x-text="prod.category_name"></span>
                                <span class="font-extrabold text-indigo-600 dark:text-indigo-400" x-text="currency + ' ' + prod.price.toFixed(2)"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        @endif

        <!-- Proposal Line Items Table -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="list-ordered" class="w-5 h-5 text-indigo-600"></i> Deliverables &amp; Quoted Items
                </h2>
                <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 font-bold text-xs transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Custom Line
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 grid grid-cols-12 gap-3 items-center">
                        <div class="col-span-12 sm:col-span-6">
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" placeholder="Deliverable description..." required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <input type="number" step="any" min="0.01" :name="'items[' + index + '][quantity]'" x-model="item.quantity" placeholder="Qty" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs text-center focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                        </div>
                        <div class="col-span-5 sm:col-span-3">
                            <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" placeholder="Rate" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-xs text-right focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                        </div>
                        <div class="col-span-3 sm:col-span-1 flex items-center justify-end">
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="p-2 text-slate-400 hover:text-rose-600 transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Custom Additional Charges / Fees -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="coins" class="w-5 h-5 text-indigo-600"></i> Additional Charges &amp; Fees
                    </h2>
                </div>
                <button type="button" @click="addAdditionalCharge()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Extra Fee
                </button>
            </div>

            <div class="space-y-3" x-show="additionalCharges.length > 0">
                <template x-for="(charge, cIdx) in additionalCharges" :key="cIdx">
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 grid grid-cols-12 gap-3 items-center">
                        <div class="col-span-12 sm:col-span-5">
                            <input type="text" :name="'additional_charges[' + cIdx + '][name]'" x-model="charge.name" placeholder="Fee Name..." required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus:outline-none">
                        </div>
                        <div class="col-span-5 sm:col-span-3">
                            <select :name="'additional_charges[' + cIdx + '][type]'" x-model="charge.type"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus:outline-none">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-span-5 sm:col-span-3">
                            <input type="number" step="any" min="0" :name="'additional_charges[' + cIdx + '][value]'" x-model="charge.value" placeholder="0.00" required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-right text-slate-900 dark:text-white focus:outline-none">
                        </div>
                        <div class="col-span-2 sm:col-span-1 flex items-center justify-end">
                            <button type="button" @click="removeAdditionalCharge(cIdx)" class="p-2 text-slate-400 hover:text-rose-600 transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Notes, Terms & Summary Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left: Notes & Terms -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-4">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-2">Terms &amp; Notes</h3>
                
                <div>
                    <label for="terms" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Proposal Terms &amp; Conditions</label>
                    <textarea name="terms" id="terms" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">{{ old('terms', $estimate->terms) }}</textarea>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Internal / Proposal Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">{{ old('notes', $estimate->notes) }}</textarea>
                </div>
            </div>

            <!-- Right: Calculation Breakdown & Submit -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs flex flex-col justify-between space-y-6">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Summary &amp; Quoted Totals</h3>

                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="currency + ' ' + subtotal.toFixed(2)"></span>
                    </div>

                    <div class="flex justify-between items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-600 dark:text-slate-400">Discount (%)</span>
                            <input type="number" step="any" min="0" max="100" name="discount_rate" x-model="discountRate" placeholder="0"
                                   class="w-16 px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-right focus:outline-none">
                        </div>
                        <span class="font-semibold text-rose-600" x-text="'-' + currency + ' ' + discountAmount.toFixed(2)"></span>
                    </div>

                    <div class="flex justify-between items-center gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-600 dark:text-slate-400">Tax / VAT (%)</span>
                            <input type="number" step="any" min="0" max="100" name="tax_rate" x-model="taxRate" placeholder="0"
                                   class="w-16 px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-right focus:outline-none">
                        </div>
                        <span class="font-semibold text-slate-900 dark:text-white" x-text="currency + ' ' + taxAmount.toFixed(2)"></span>
                    </div>

                    <template x-for="(charge, cIdx) in additionalCharges" :key="'sum-' + cIdx">
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400" x-show="charge.name">
                            <span x-text="charge.name + (charge.type === 'percentage' ? ' (' + charge.value + '%)' : '')"></span>
                            <span class="font-semibold text-slate-900 dark:text-white" 
                                  x-text="'+' + currency + ' ' + (charge.type === 'percentage' ? (taxableAmount * ((parseFloat(charge.value) || 0) / 100)).toFixed(2) : (parseFloat(charge.value) || 0).toFixed(2))"></span>
                        </div>
                    </template>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center">
                        <span class="text-base font-extrabold text-slate-900 dark:text-white">Quoted Total</span>
                        <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400" x-text="currency + ' ' + grandTotal.toFixed(2)"></span>
                    </div>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Update Quotation Proposal</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
