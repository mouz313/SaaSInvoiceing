@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-24 sm:pb-8" 
     x-data="{
        taxRate: 0,
        discountRate: 0,
        selectedClientId: '{{ old('client_id', request('client_id', '')) }}',
        currency: '{{ old('currency', Auth::user()->default_currency ?? 'USD') }}',
        selectedStyle: 'minimalist',
        selectedLogoId: {{ old('logo_id', 'null') }},
        logos: {{ json_encode($logos->map(fn($l) => ['id' => $l->id, 'filename' => $l->filename, 'url' => $l->url])) }},
        logoUploading: false,
        logoError: '',
        
        // Modals state
        logoModalOpen: false,
        styleModalOpen: false,
        catalogModalOpen: false,
        chargesModalOpen: false,
        unbilledModalOpen: false,
        unbilledLoading: false,

        // Unbilled Time & Expenses
        unbilledTime: [],
        unbilledExpenses: [],
        checkedTime: {},
        checkedExpenses: {},
        selectedTimeIds: [],
        selectedExpenseIds: [],

        // Notes Tab
        activeNotesTab: 'notes',

        get selectedLogo() {
            return this.logos.find(l => l.id === this.selectedLogoId) || null;
        },
        get logoCount() { return this.logos.length; },
        get maxLogos() { return 10; },
        selectLogo(id) {
            this.selectedLogoId = this.selectedLogoId === id ? null : id;
        },
        async uploadLogo(event) {
            const file = event.target.files[0];
            if (!file) return;
            if (this.logoCount >= this.maxLogos) {
                this.logoError = 'Maximum 10 logos reached. Delete one first.';
                return;
            }
            this.logoUploading = true;
            this.logoError = '';
            const formData = new FormData();
            formData.append('logo', file);
            formData.append('_token', document.querySelector('meta[name=csrf-token]').content);
            try {
                const res = await fetch('{{ route('logos.store') }}', { method: 'POST', body: formData });
                const data = await res.json();
                if (!res.ok) {
                    this.logoError = data.error || 'Upload failed.';
                } else {
                    this.logos.push(data);
                    this.selectedLogoId = data.id;
                }
            } catch(e) {
                this.logoError = 'Upload failed. Please try again.';
            } finally {
                this.logoUploading = false;
                event.target.value = '';
            }
        },
        async deleteLogo(logo, event) {
            event.stopPropagation();
            if (!confirm('Delete this logo?')) return;
            try {
                await fetch('/logos/' + logo.id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                });
                this.logos = this.logos.filter(l => l.id !== logo.id);
                if (this.selectedLogoId === logo.id) this.selectedLogoId = null;
            } catch(e) {}
        },

        // Products Catalog
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
        addProductToInvoice(p) {
            this.items.push({
                description: p.name + (p.description ? ' — ' + p.description : ''),
                quantity: 1,
                unit_price: parseFloat(p.price) || 0,
                is_custom: true
            });
            this.catalogModalOpen = false;
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },

        // Unbilled Modal Actions
        async openUnbilledModal() {
            if (!this.selectedClientId) {
                alert('Please select a client from the dropdown first.');
                return;
            }
            this.unbilledModalOpen = true;
            this.unbilledLoading = true;
            try {
                const res = await fetch('/clients/' + this.selectedClientId + '/unbilled-items');
                const data = await res.json();
                this.unbilledTime = data.time_entries || [];
                this.unbilledExpenses = data.expenses || [];
                this.checkedTime = {};
                this.checkedExpenses = {};
                this.unbilledTime.forEach(t => this.checkedTime[t.id] = true);
                this.unbilledExpenses.forEach(e => this.checkedExpenses[e.id] = true);
            } catch (e) {
                alert('Could not fetch unbilled items for this client.');
            } finally {
                this.unbilledLoading = false;
            }
        },
        importUnbilled() {
            // Remove initial empty row if not filled
            if (this.items.length === 1 && !this.items[0].description) {
                this.items = [];
            }
            this.unbilledTime.forEach(t => {
                if (this.checkedTime[t.id]) {
                    this.items.push({
                        description: 'Time: ' + (t.project_name ? t.project_name + ' — ' : '') + t.task_description + ' (' + t.hours + ' hrs @ $' + parseFloat(t.hourly_rate).toFixed(2) + '/hr)',
                        quantity: parseFloat(t.hours) || 1,
                        unit_price: parseFloat(t.hourly_rate) || 0,
                        is_custom: true
                    });
                    if (!this.selectedTimeIds.includes(t.id)) {
                        this.selectedTimeIds.push(t.id);
                    }
                }
            });
            this.unbilledExpenses.forEach(e => {
                if (this.checkedExpenses[e.id]) {
                    this.items.push({
                        description: 'Expense: ' + e.category + ' — ' + e.description,
                        quantity: 1,
                        unit_price: parseFloat(e.amount) || 0,
                        is_custom: true
                    });
                    if (!this.selectedExpenseIds.includes(e.id)) {
                        this.selectedExpenseIds.push(e.id);
                    }
                }
            });
            this.unbilledModalOpen = false;
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },

        // Items - Starts with clean blank row ready for Product selection or Custom entry
        items: [
            { description: '', quantity: 1, unit_price: 0.00, is_custom: false }
        ],
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0.00, is_custom: false });
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },
        removeItem(index) {
            this.items.splice(index, 1);
        },
        handleItemSelect(item, event) {
            const val = event.target.value;
            if (!val) return;
            if (val === 'custom') {
                item.is_custom = true;
                item.description = '';
                item.unit_price = 0;
            } else {
                const prod = this.products.find(p => String(p.id) === String(val));
                if (prod) {
                    item.description = prod.name + (prod.description ? ' — ' + prod.description : '');
                    item.unit_price = parseFloat(prod.price) || 0;
                    item.is_custom = true;
                }
            }
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },

        // Custom Taxes & Charges
        additionalCharges: [],
        newChargeName: '',
        newChargeType: 'fixed',
        newChargeValue: 0,
        addChargeFromModal() {
            if (!this.newChargeName.trim()) return;
            this.additionalCharges.push({
                name: this.newChargeName.trim(),
                type: this.newChargeType,
                value: parseFloat(this.newChargeValue) || 0
            });
            this.newChargeName = '';
            this.newChargeValue = 0;
            this.chargesModalOpen = false;
        },
        removeAdditionalCharge(index) {
            this.additionalCharges.splice(index, 1);
        },

        // Calculation Getters
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('invoices.index') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Create New Invoice</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Quickly configure client, line items, and generate a professional invoice.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a href="{{ route('invoices.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
            <button type="submit" form="invoice-form" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-600/20 hover:scale-[1.01] active:scale-[0.99] transition">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Save Invoice</span>
            </button>
        </div>
    </div>

    @if($clients->isEmpty())
    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 text-amber-900 dark:text-amber-300 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs sm:text-sm">
        <div class="flex items-center gap-2.5">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 shrink-0"></i>
            <span>No clients found. You need at least one client profile to issue an invoice.</span>
        </div>
        <a href="{{ route('clients.create') }}" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs self-start sm:self-auto shrink-0 transition">
            + Add Client Now
        </a>
    </div>
    @endif

    <form id="invoice-form" method="POST" action="{{ route('invoices.store') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="style" :value="selectedStyle">
        <input type="hidden" name="logo_id" :value="selectedLogoId">

        <!-- Unified Card 1: Invoice Header & Client Profile -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-7 shadow-xs space-y-6">
            <!-- Header bar with branding & template pills -->
            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                    <h2 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                        Invoice Overview
                    </h2>
                </div>

                <!-- Customizer Triggers (Logo & Template Modals) -->
                <div class="flex items-center gap-2">
                    <!-- Logo Modal Trigger -->
                    <button type="button" @click="logoModalOpen = true" 
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold border transition"
                            :class="selectedLogoId ? 'border-indigo-300 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300'">
                        <template x-if="selectedLogo">
                            <img :src="selectedLogo.url" class="w-4 h-4 rounded object-contain">
                        </template>
                        <template x-if="!selectedLogo">
                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                        </template>
                        <span x-text="selectedLogo ? 'Logo: Selected' : '+ Add Logo'"></span>
                    </button>

                    <!-- Template Style Trigger -->
                    <button type="button" @click="styleModalOpen = true" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 dark:border-slate-700 hover:border-blue-300 bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 transition">
                        <i data-lucide="palette" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span>Template: <strong class="capitalize" x-text="selectedStyle"></strong></span>
                    </button>
                </div>
            </div>

            <!-- Primary Metadata Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                <!-- Client Selector (Span 2) -->
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Client <span class="text-rose-500">*</span>
                        </label>
                        <a href="{{ route('clients.create') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                            <i data-lucide="user-plus" class="w-3 h-3"></i> New Client
                        </a>
                    </div>
                    <select name="client_id" x-model="selectedClientId" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none transition">
                        <option value="">Choose a client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} {{ $client->company_name ? "({$client->company_name})" : "" }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="text-rose-600 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Invoice Number -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Invoice # <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $defaultNumber) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-blue-600 focus:outline-none transition">
                    @error('invoice_number') <p class="text-rose-600 text-xs mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>

                <!-- Currency -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Currency <span class="text-rose-500">*</span>
                    </label>
                    <select name="currency" x-model="currency" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-blue-600 focus:outline-none transition">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="PKR">PKR (₨)</option>
                        <option value="INR">INR (₹)</option>
                        <option value="AED">AED (د.إ)</option>
                        <option value="SAR">SAR (﷼)</option>
                        <option value="CAD">CAD ($)</option>
                        <option value="AUD">AUD ($)</option>
                        <option value="JPY">JPY (¥)</option>
                        <option value="CHF">CHF (CHF)</option>
                    </select>
                </div>

                <!-- Issue Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Issue Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none transition">
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Due Date <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none transition">
                </div>

                <!-- Initial Status -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Initial Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none transition">
                        <option value="draft" selected>Draft (Not yet sent)</option>
                        <option value="sent">Sent (Awaiting payment)</option>
                        <option value="paid">Paid (Mark completed)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Unified Card 2: Line Items & Workspace -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-7 shadow-xs space-y-5">
            <!-- Line Items Header & Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                        Line Items (<span x-text="items.length"></span>)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Select a product from dropdown, enter custom item, or import logs</p>
                </div>
                
                <!-- Quick Insertion Buttons -->
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="catalogModalOpen = true"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950/50 hover:bg-blue-100 text-blue-700 dark:text-blue-300 text-xs font-bold border border-blue-200/80 dark:border-blue-900/60 transition shadow-xs">
                        <i data-lucide="package" class="w-3.5 h-3.5 text-blue-600"></i>
                        <span>Catalog Modal</span>
                    </button>
                    <button type="button" @click="openUnbilledModal()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 text-xs font-bold border border-indigo-200/80 dark:border-indigo-900/60 transition shadow-xs">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-indigo-600"></i>
                        <span>Import Time/Expenses</span>
                    </button>
                    <button type="button" @click="addItem()"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-xs">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>+ Add Item</span>
                    </button>
                </div>
            </div>

            <!-- Items Table (Desktop Header) -->
            <div class="hidden sm:grid grid-cols-12 gap-3 px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                <div class="col-span-6">Product / Description & Deliverable</div>
                <div class="col-span-2 text-right">Quantity</div>
                <div class="col-span-2 text-right">Unit Price (<span x-text="currency"></span>)</div>
                <div class="col-span-1 text-right">Total</div>
                <div class="col-span-1 text-center">Action</div>
            </div>

            <!-- Line Items List (Responsive Card on Mobile, Grid Row on Desktop) -->
            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-3.5 sm:p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 hover:border-slate-300 dark:hover:border-slate-600 transition">
                        
                        <!-- Mobile View (<640px) -->
                        <div class="sm:hidden space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400" x-text="'Item #' + (index + 1)"></span>
                                <button type="button" @click="removeItem(index)"
                                        class="p-1 text-slate-400 hover:text-rose-600 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>

                            <!-- Product Selector Dropdown or Custom Text Input -->
                            <div x-show="!item.is_custom" class="w-full">
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Select Product or Custom</label>
                                <select @change="handleItemSelect(item, $event)"
                                        class="w-full px-3 py-2 rounded-xl border border-blue-300 dark:border-blue-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:ring-2 focus:ring-blue-600 focus:outline-none shadow-2xs">
                                    <option value="">-- Choose Product or Custom Item --</option>
                                    <option value="custom">✏️ Enter Custom Item...</option>
                                    @if($products->isNotEmpty())
                                        @php
                                            $groupedProducts = $products->groupBy(fn($p) => $p->category?->name ?? 'General Products');
                                        @endphp
                                        @foreach($groupedProducts as $categoryName => $catProds)
                                            <optgroup label="{{ $categoryName }}">
                                                @foreach($catProds as $prod)
                                                    <option value="{{ $prod->id }}">
                                                        {{ $prod->name }} — {{ $prod->price }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div x-show="item.is_custom" class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase">Item Description</label>
                                    <button type="button" @click="item.is_custom = false; item.description = ''; item.unit_price = 0" 
                                            class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                        ↺ Pick from products
                                    </button>
                                </div>
                                <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required placeholder="Type custom item / service..."
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Quantity</label>
                                    <input type="number" step="1" min="1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required
                                           class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-blue-600 focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Unit Price (<span x-text="currency"></span>)</label>
                                    <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required
                                           class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-blue-600 focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                </div>
                            </div>
                            <div class="pt-1.5 flex items-center justify-between text-xs font-bold border-t border-slate-200/60 dark:border-slate-700/60">
                                <span class="text-slate-500">Line Amount:</span>
                                <span class="text-slate-900 dark:text-white" x-text="currency + ' ' + (((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0))).toFixed(2)"></span>
                            </div>
                        </div>

                        <!-- Desktop View (>=640px) -->
                        <div class="hidden sm:grid grid-cols-12 gap-3 items-center">
                            <div class="col-span-6">
                                <!-- Dropdown if not custom -->
                                <div x-show="!item.is_custom" class="w-full">
                                    <select @change="handleItemSelect(item, $event)"
                                            class="w-full px-3 py-2.5 rounded-xl border border-blue-300 dark:border-blue-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none transition shadow-2xs">
                                        <option value="">-- Choose Product or Custom Item --</option>
                                        <option value="custom">✏️ Enter Custom Item...</option>
                                        @if($products->isNotEmpty())
                                            @php
                                                $groupedProducts = $products->groupBy(fn($p) => $p->category?->name ?? 'General Products');
                                            @endphp
                                            @foreach($groupedProducts as $categoryName => $catProds)
                                                <optgroup label="{{ $categoryName }}">
                                                    @foreach($catProds as $prod)
                                                        <option value="{{ $prod->id }}">
                                                            {{ $prod->name }} — {{ $prod->price }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <!-- Custom Text input if selected or custom -->
                                <div x-show="item.is_custom" class="w-full space-y-1">
                                    <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required placeholder="Type custom item / service..."
                                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-600 focus:outline-none transition">
                                    <button type="button" @click="item.is_custom = false; item.description = ''; item.unit_price = 0" 
                                            class="text-[10px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 pl-1">
                                        <i data-lucide="layers" class="w-3 h-3"></i> Choose from products dropdown
                                    </button>
                                </div>
                            </div>

                            <div class="col-span-2">
                                <input type="number" step="1" min="1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required placeholder="1"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-right focus:ring-2 focus:ring-blue-600 focus:outline-none transition [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                            </div>
                            <div class="col-span-2">
                                <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required placeholder="0.00"
                                       class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-right focus:ring-2 focus:ring-blue-600 focus:outline-none transition [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                            </div>
                            <div class="col-span-1 text-right font-extrabold text-xs text-slate-900 dark:text-white truncate">
                                <span x-text="(((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0))).toFixed(2)"></span>
                            </div>
                            <div class="col-span-1 text-center">
                                <button type="button" @click="removeItem(index)" 
                                        class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </template>
            </div>

            <!-- Empty State if no items -->
            <div x-show="items.length === 0" class="py-8 text-center rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-800 text-slate-400 space-y-2">
                <i data-lucide="shopping-cart" class="w-8 h-8 mx-auto text-slate-300"></i>
                <p class="text-xs">No items added yet. Click "+ Add Item" to select a product or enter a custom service.</p>
                <button type="button" @click="addItem()" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                    + Add Item Now
                </button>
            </div>

            <!-- Bottom Add Item Row -->
            <div class="pt-2 flex items-center justify-between">
                <button type="button" @click="addItem()" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 hover:border-blue-500 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 text-xs font-bold transition">
                    <i data-lucide="plus" class="w-4 h-4 text-blue-600"></i>
                    <span>+ Add Another Line</span>
                </button>
                <span class="text-xs font-semibold text-slate-400" x-text="'Subtotal: ' + currency + ' ' + subtotal.toFixed(2)"></span>
            </div>
        </div>

        <!-- Unified Card 3: Notes, Extra Taxes & Summary Totals -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Side: Notes & Payment Terms (Span 7) -->
            <div class="lg:col-span-7 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl text-xs font-bold">
                        <button type="button" @click="activeNotesTab = 'notes'"
                                :class="activeNotesTab === 'notes' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                                class="px-3 py-1.5 rounded-lg transition">
                            Notes &amp; Terms
                        </button>
                        <button type="button" @click="activeNotesTab = 'payment'"
                                :class="activeNotesTab === 'payment' ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white'"
                                class="px-3 py-1.5 rounded-lg transition">
                            Bank / Remittance
                        </button>
                    </div>
                    <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">Printed on invoice</span>
                </div>

                <!-- Tab 1: Invoice Notes -->
                <div x-show="activeNotesTab === 'notes'">
                    <textarea name="notes" id="notes" rows="4" placeholder="e.g. Thank you for your business! Payment is due within 14 days of invoice receipt..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none leading-relaxed transition">{{ old('notes', Auth::user()->default_notes) }}</textarea>
                </div>

                <!-- Tab 2: Bank / Payment Instructions -->
                <div x-show="activeNotesTab === 'payment'">
                    <textarea name="payment_instructions" id="payment_instructions" rows="4" placeholder="e.g. Wire transfer: Bank Name, Routing/SWIFT, IBAN/Account Number..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs sm:text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none leading-relaxed transition">{{ old('payment_instructions', Auth::user()->default_payment_instructions) }}</textarea>
                </div>

                <!-- Direct Email Checkbox -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="send_email_now" value="1" {{ old('send_email_now') ? 'checked' : '' }}
                               class="w-4 h-4 mt-0.5 rounded text-blue-600 focus:ring-blue-500 border-slate-300 dark:border-slate-600">
                        <div>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-blue-600"></i> Dispatch Email to Client Instantly
                            </span>
                            <span class="text-[11px] text-slate-400 block mt-0.5">
                                Client receives PDF attachment &amp; secure online payment link upon save.
                            </span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Right Side: Calculation Breakdown (Span 5) -->
            <div class="lg:col-span-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-xs space-y-4">
                <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 pb-2 border-b border-slate-100 dark:border-slate-800">
                    Summary Breakdown
                </h3>

                <div class="space-y-3.5 text-xs sm:text-sm">
                    <!-- Subtotal -->
                    <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                        <span class="font-medium">Subtotal</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="currency + ' ' + subtotal.toFixed(2)"></span>
                    </div>

                    <!-- Discount with Modern UI Stepper -->
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span class="font-medium">Discount</span>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-0.5 shadow-2xs">
                                <button type="button" @click="discountRate = Math.max(0, (parseFloat(discountRate) || 0) - 1)" 
                                        class="w-6 h-6 rounded-lg bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-600 flex items-center justify-center text-xs font-black shadow-xs transition hover:scale-105 active:scale-95">
                                    <i data-lucide="minus" class="w-3 h-3"></i>
                                </button>
                                <div class="flex items-center px-1.5">
                                    <input type="number" step="any" min="0" max="100" name="discount_rate" x-model="discountRate" placeholder="0"
                                           class="w-10 text-center font-bold text-xs text-slate-800 dark:text-white bg-transparent focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                    <span class="text-xs font-bold text-slate-400">%</span>
                                </div>
                                <button type="button" @click="discountRate = Math.min(100, (parseFloat(discountRate) || 0) + 1)" 
                                        class="w-6 h-6 rounded-lg bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-600 flex items-center justify-center text-xs font-black shadow-xs transition hover:scale-105 active:scale-95">
                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                </button>
                            </div>
                            <span class="font-bold text-rose-600 min-w-16 text-right" x-text="'-' + currency + ' ' + discountAmount.toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Tax / VAT with Modern UI Stepper -->
                    <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                        <span class="font-medium">Tax / VAT</span>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-0.5 shadow-2xs">
                                <button type="button" @click="taxRate = Math.max(0, (parseFloat(taxRate) || 0) - 1)" 
                                        class="w-6 h-6 rounded-lg bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-600 flex items-center justify-center text-xs font-black shadow-xs transition hover:scale-105 active:scale-95">
                                    <i data-lucide="minus" class="w-3 h-3"></i>
                                </button>
                                <div class="flex items-center px-1.5">
                                    <input type="number" step="any" min="0" max="100" name="tax_rate" x-model="taxRate" placeholder="0"
                                           class="w-10 text-center font-bold text-xs text-slate-800 dark:text-white bg-transparent focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                    <span class="text-xs font-bold text-slate-400">%</span>
                                </div>
                                <button type="button" @click="taxRate = Math.min(100, (parseFloat(taxRate) || 0) + 1)" 
                                        class="w-6 h-6 rounded-lg bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-600 flex items-center justify-center text-xs font-black shadow-xs transition hover:scale-105 active:scale-95">
                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                </button>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white min-w-16 text-right" x-text="'+' + currency + ' ' + taxAmount.toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Additional Charges Chips / Trigger -->
                    <div class="pt-2 border-t border-dashed border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Extra Charges</span>
                            <button type="button" @click="chargesModalOpen = true" 
                                    class="text-[11px] text-blue-600 dark:text-blue-400 font-bold hover:underline flex items-center gap-1">
                                <i data-lucide="plus" class="w-3 h-3"></i> Add Charge / Fee
                            </button>
                        </div>

                        <!-- Active Additional Charges List -->
                        <template x-for="(charge, cIdx) in additionalCharges" :key="cIdx">
                            <div class="flex items-center justify-between py-1 text-xs text-slate-600 dark:text-slate-400">
                                <div class="flex items-center gap-1.5">
                                    <button type="button" @click="removeAdditionalCharge(cIdx)" class="text-rose-500 hover:text-rose-700">
                                        <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <span class="font-medium" x-text="charge.name + (charge.type === 'percentage' ? ' (' + charge.value + '%)' : '')"></span>
                                </div>
                                <span class="font-semibold text-slate-900 dark:text-white" 
                                      x-text="'+' + currency + ' ' + (charge.type === 'percentage' ? (taxableAmount * ((parseFloat(charge.value) || 0) / 100)).toFixed(2) : (parseFloat(charge.value) || 0).toFixed(2))"></span>
                                <!-- Hidden form inputs for submission -->
                                <input type="hidden" :name="'additional_charges[' + cIdx + '][name]'" :value="charge.name">
                                <input type="hidden" :name="'additional_charges[' + cIdx + '][type]'" :value="charge.type">
                                <input type="hidden" :name="'additional_charges[' + cIdx + '][value]'" :value="charge.value">
                            </div>
                        </template>

                        <div x-show="additionalCharges.length === 0" class="text-[11px] text-slate-400 italic">
                            No shipping or extra charges added.
                        </div>
                    </div>

                    <!-- Grand Total Banner -->
                    <div class="pt-3 border-t-2 border-slate-200 dark:border-slate-800 flex justify-between items-center">
                        <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white">Total Due</span>
                        <span class="text-xl sm:text-2xl font-black text-blue-600 dark:text-blue-400" x-text="currency + ' ' + grandTotal.toFixed(2)"></span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-600/20 hover:scale-[1.01] active:scale-[0.99] transition flex items-center justify-center gap-2">
                    <i data-lucide="file-check" class="w-4 h-4"></i>
                    <span>Generate &amp; Save Invoice</span>
                </button>
            </div>

        </div>

        <!-- Hidden inputs for linked time entries & expenses -->
        <template x-for="id in selectedTimeIds">
            <input type="hidden" name="time_entry_ids[]" :value="id">
        </template>
        <template x-for="id in selectedExpenseIds">
            <input type="hidden" name="expense_ids[]" :value="id">
        </template>

        <!-- ================= MODAL 1: LOGO SELECTOR ================= -->
        <div x-show="logoModalOpen" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-850 rounded-3xl max-w-xl w-full p-6 shadow-2xl relative border border-slate-200 dark:border-slate-800" @click.outside="logoModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Company Logo Branding</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pick or upload a logo for your invoice header</p>
                    </div>
                    <button type="button" @click="logoModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <!-- Logos Grid -->
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3 max-h-64 overflow-y-auto pr-1">
                        <template x-for="logo in logos" :key="logo.id">
                            <div @click="selectLogo(logo.id)"
                                 :class="selectedLogoId === logo.id ? 'ring-2 ring-blue-600 bg-blue-50/50 dark:bg-blue-950/40 border-blue-500' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                                 class="relative group cursor-pointer rounded-2xl border p-2.5 flex items-center justify-center aspect-square transition overflow-hidden">
                                <img :src="logo.url" :alt="logo.filename" class="max-h-full max-w-full object-contain">
                                <!-- Tick -->
                                <div x-show="selectedLogoId === logo.id"
                                     class="absolute top-1.5 left-1.5 w-4 h-4 bg-blue-600 rounded-full flex items-center justify-center text-white text-[10px] font-bold">
                                    ✓
                                </div>
                                <!-- Delete Button -->
                                <button type="button" @click="deleteLogo(logo, $event)"
                                        class="absolute top-1.5 right-1.5 w-5 h-5 bg-rose-600 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition shadow-xs">
                                    <i data-lucide="trash" class="w-2.5 h-2.5"></i>
                                </button>
                            </div>
                        </template>

                        <!-- Upload Box -->
                        <label x-show="logoCount < maxLogos"
                               :class="logoUploading ? 'opacity-50 cursor-wait' : 'cursor-pointer hover:border-blue-500 hover:bg-blue-50/20'"
                                class="rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 flex flex-col items-center justify-center aspect-square transition text-slate-400 gap-1">
                            <i data-lucide="upload" class="w-5 h-5"></i>
                            <span class="text-[10px] font-bold uppercase tracking-wider">Upload</span>
                            <input type="file" class="sr-only" accept="image/*" @change="uploadLogo($event)" :disabled="logoUploading">
                        </label>
                    </div>

                    <p x-show="logoError" x-text="logoError" class="text-xs text-rose-500 font-semibold"></p>
                </div>

                <div class="mt-6 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <button type="button" @click="selectedLogoId = null" class="text-xs text-slate-400 hover:text-slate-600 underline">
                        Remove Logo
                    </button>
                    <button type="button" @click="logoModalOpen = false" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                        Done
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= MODAL 2: TEMPLATE SELECTOR ================= -->
        <div x-show="styleModalOpen" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-850 rounded-3xl max-w-3xl w-full p-6 shadow-2xl relative border border-slate-200 dark:border-slate-800" @click.outside="styleModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Choose Invoice Template</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Select the aesthetic layout for web and PDF generation</p>
                    </div>
                    <button type="button" @click="styleModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Template 4 Cards Grid -->
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Minimalist -->
                    <div @click="selectedStyle = 'minimalist'" 
                         :class="selectedStyle === 'minimalist' ? 'ring-2 ring-blue-600 bg-blue-50/40 dark:bg-blue-950/20 border-blue-500' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                         class="cursor-pointer rounded-2xl border p-4 transition flex flex-col justify-between">
                        <div>
                            <div class="h-20 rounded-xl bg-slate-100 dark:bg-slate-800 p-2.5 flex flex-col justify-between mb-3 border border-slate-200/60 dark:border-slate-700/60">
                                <div class="w-12 h-2 rounded bg-slate-900 dark:bg-white"></div>
                                <div class="w-full h-1.5 rounded bg-slate-300 dark:bg-slate-600"></div>
                                <div class="w-8 h-2 rounded bg-blue-600 self-end"></div>
                            </div>
                            <h4 class="font-black text-sm text-slate-900 dark:text-white">Modern Minimalist</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Monochrome whitespace, clean corporate typography.</p>
                        </div>
                        <div class="mt-3 text-xs font-bold text-blue-600 flex items-center gap-1" x-show="selectedStyle === 'minimalist'">
                            <i data-lucide="check" class="w-4 h-4"></i> Selected
                        </div>
                    </div>

                    <!-- Corporate Classic -->
                    <div @click="selectedStyle = 'corporate'" 
                         :class="selectedStyle === 'corporate' ? 'ring-2 ring-blue-600 bg-blue-50/40 dark:bg-blue-950/20 border-blue-500' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                         class="cursor-pointer rounded-2xl border p-4 transition flex flex-col justify-between">
                        <div>
                            <div class="h-20 rounded-xl bg-slate-100 dark:bg-slate-800 p-2.5 flex flex-col justify-between mb-3 border border-slate-200/60 dark:border-slate-700/60">
                                <div class="w-full h-3.5 rounded bg-slate-900 text-white text-[7px] flex items-center px-1 font-bold">OFFICIAL</div>
                                <div class="w-full h-1.5 rounded bg-slate-300 dark:bg-slate-600"></div>
                                <div class="w-16 h-2 rounded bg-slate-400"></div>
                            </div>
                            <h4 class="font-black text-sm text-slate-900 dark:text-white">Corporate Classic</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Deep header bar, formal borders, executive layout.</p>
                        </div>
                        <div class="mt-3 text-xs font-bold text-blue-600 flex items-center gap-1" x-show="selectedStyle === 'corporate'">
                            <i data-lucide="check" class="w-4 h-4"></i> Selected
                        </div>
                    </div>

                    <!-- Creative Bold -->
                    <div @click="selectedStyle = 'creative'" 
                         :class="selectedStyle === 'creative' ? 'ring-2 ring-blue-600 bg-blue-50/40 dark:bg-blue-950/20 border-blue-500' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                         class="cursor-pointer rounded-2xl border p-4 transition flex flex-col justify-between">
                        <div>
                            <div class="h-20 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 p-2.5 flex flex-col justify-between mb-3 text-white">
                                <div class="w-10 h-2 rounded bg-white/50"></div>
                                <div class="w-full h-1.5 rounded bg-white/20"></div>
                                <div class="w-10 h-2 rounded bg-white/40 self-end"></div>
                            </div>
                            <h4 class="font-black text-sm text-slate-900 dark:text-white">Creative Bold</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Vibrant gradient banner, carded item rows, design agency style.</p>
                        </div>
                        <div class="mt-3 text-xs font-bold text-blue-600 flex items-center gap-1" x-show="selectedStyle === 'creative'">
                            <i data-lucide="check" class="w-4 h-4"></i> Selected
                        </div>
                    </div>

                    <!-- Clean Grid -->
                    <div @click="selectedStyle = 'grid'" 
                         :class="selectedStyle === 'grid' ? 'ring-2 ring-blue-600 bg-blue-50/40 dark:bg-blue-950/20 border-blue-500' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'"
                         class="cursor-pointer rounded-2xl border p-4 transition flex flex-col justify-between">
                        <div>
                            <div class="h-20 rounded-xl bg-slate-100 dark:bg-slate-800 p-2.5 border-2 border-slate-900 dark:border-slate-600 flex flex-col justify-between mb-3">
                                <div class="w-full h-2 rounded bg-slate-900 dark:bg-slate-400"></div>
                                <div class="grid grid-cols-2 gap-1">
                                    <div class="h-1.5 bg-slate-300 dark:bg-slate-600 rounded"></div>
                                    <div class="h-1.5 bg-slate-300 dark:bg-slate-600 rounded"></div>
                                </div>
                                <div class="w-12 h-2 rounded bg-slate-900 dark:bg-slate-300 self-end"></div>
                            </div>
                            <h4 class="font-black text-sm text-slate-900 dark:text-white">Clean Grid</h4>
                            <p class="text-xs text-slate-400 mt-0.5">Boxed table format, monospace invoice numbers, tech billing.</p>
                        </div>
                        <div class="mt-3 text-xs font-bold text-blue-600 flex items-center gap-1" x-show="selectedStyle === 'grid'">
                            <i data-lucide="check" class="w-4 h-4"></i> Selected
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="button" @click="styleModalOpen = false" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                        Apply Template
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= MODAL 3: PRODUCT CATALOG PICKER ================= -->
        <div x-show="catalogModalOpen" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-850 rounded-3xl max-w-2xl w-full p-6 shadow-2xl relative border border-slate-200 dark:border-slate-800" @click.outside="catalogModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                            <span>1-Click Add From Products Catalog</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Click any product to add it instantly to your invoice</p>
                    </div>
                    <button type="button" @click="catalogModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <!-- Search & Add Product link -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                            <input type="text" x-model="catalogSearch" placeholder="Search by product name or description..." 
                                   class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                        <a href="{{ route('products.create') }}" target="_blank" class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold shrink-0 transition flex items-center gap-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i> New
                        </a>
                    </div>

                    <!-- Category Pills -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <button type="button" @click="selectedCatalogCategory = 'all'" 
                                :class="selectedCatalogCategory === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                                class="px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap transition">
                            All (<span x-text="products.length"></span>)
                        </button>
                        @foreach($categories as $category)
                            <button type="button" @click="selectedCatalogCategory = '{{ $category->id }}'" 
                                    :class="selectedCatalogCategory === '{{ $category->id }}' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold whitespace-nowrap transition">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Products Grid -->
                    <div class="max-h-72 overflow-y-auto pr-1 space-y-2">
                        <template x-if="filteredProducts.length === 0">
                            <p class="text-xs text-slate-400 italic py-6 text-center">No products found matching your search.</p>
                        </template>
                        <template x-for="prod in filteredProducts" :key="prod.id">
                            <div class="p-3 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-blue-400 dark:hover:border-blue-500 bg-white dark:bg-slate-800/80 flex items-center justify-between gap-3 transition">
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs sm:text-sm text-slate-900 dark:text-white truncate" x-text="prod.name"></div>
                                    <div class="text-[11px] text-slate-400 truncate mt-0.5" x-text="prod.category_name + (prod.description ? ' • ' + prod.description : '')"></div>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <span class="font-black text-xs sm:text-sm text-slate-900 dark:text-white" x-text="currency + ' ' + parseFloat(prod.price).toFixed(2)"></span>
                                    <button type="button" @click="addProductToInvoice(prod)" 
                                            class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1 shadow-xs">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button type="button" @click="catalogModalOpen = false" class="px-5 py-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold transition">
                        Close Catalog
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= MODAL 4: CUSTOM CHARGES & TAXES ================= -->
        <div x-show="chargesModalOpen" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-850 rounded-3xl max-w-md w-full p-6 shadow-2xl relative border border-slate-200 dark:border-slate-800" @click.outside="chargesModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Add Extra Charge / Tax</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Shipping fee, delivery, service fee, packaging, etc.</p>
                    </div>
                    <button type="button" @click="chargesModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Fee Name *</label>
                        <input type="text" x-model="newChargeName" placeholder="e.g. Express Shipping, Packaging Fee"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Fee Type</label>
                            <select x-model="newChargeType" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none">
                                <option value="fixed">Fixed Amount (<span x-text="currency"></span>)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Amount / Value</label>
                            <input type="number" step="any" min="0" x-model="newChargeValue" placeholder="0.00"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="chargesModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100">
                        Cancel
                    </button>
                    <button type="button" @click="addChargeFromModal()" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                        Add to Invoice
                    </button>
                </div>
            </div>
        </div>

        <!-- ================= MODAL 5: UNBILLED TIME & EXPENSES ================= -->
        <div x-show="unbilledModalOpen" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-850 rounded-3xl max-w-2xl w-full p-6 shadow-2xl relative border border-slate-200 dark:border-slate-800" @click.outside="unbilledModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Import Unbilled Time &amp; Expenses</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Select logged hours or costs to convert into invoice line items</p>
                    </div>
                    <button type="button" @click="unbilledModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div x-show="unbilledLoading" class="py-12 text-center text-xs text-slate-400">
                    <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-blue-600 border-t-transparent mb-2"></div>
                    <p>Fetching unbilled records...</p>
                </div>

                <div x-show="!unbilledLoading" class="mt-4 space-y-5 max-h-80 overflow-y-auto pr-1">
                    <!-- Time Entries Section -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                            <i data-lucide="clock" class="w-3.5 h-3.5 text-blue-600"></i>
                            Logged Time Entries (<span x-text="unbilledTime.length"></span>)
                        </h4>
                        <div class="space-y-2">
                            <template x-for="entry in unbilledTime" :key="entry.id">
                                <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition">
                                    <input type="checkbox" x-model="checkedTime[entry.id]" class="w-4 h-4 mt-0.5 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                    <div class="flex-1 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-900 dark:text-white" x-text="entry.project_name ? entry.project_name + ' — ' + entry.task_description : entry.task_description"></span>
                                            <span class="font-black text-blue-600" x-text="currency + ' ' + (parseFloat(entry.total_amount) || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5" x-text="entry.date + ' • ' + entry.hours + ' hrs @ ' + currency + ' ' + entry.hourly_rate + '/hr'"></div>
                                    </div>
                                </label>
                            </template>
                            <div x-show="unbilledTime.length === 0" class="text-xs text-slate-400 italic py-1">
                                No unbilled time entries found for this client.
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Section -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
                            <i data-lucide="receipt" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Billable Expenses (<span x-text="unbilledExpenses.length"></span>)
                        </h4>
                        <div class="space-y-2">
                            <template x-for="exp in unbilledExpenses" :key="exp.id">
                                <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60 cursor-pointer transition">
                                    <input type="checkbox" x-model="checkedExpenses[exp.id]" class="w-4 h-4 mt-0.5 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                                    <div class="flex-1 text-xs">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-slate-900 dark:text-white" x-text="exp.category + ' — ' + exp.description"></span>
                                            <span class="font-black text-emerald-600" x-text="currency + ' ' + (parseFloat(exp.amount) || 0).toFixed(2)"></span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5" x-text="exp.expense_date"></div>
                                    </div>
                                </label>
                            </template>
                            <div x-show="unbilledExpenses.length === 0" class="text-xs text-slate-400 italic py-1">
                                No unbilled expenses found for this client.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="unbilledModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="button" @click="importUnbilled()" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                        Import Selected
                    </button>
                </div>
            </div>
        </div>

    </form>

    <!-- Mobile Sticky Action Bar (<640px) -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 px-4 py-3 shadow-2xl flex items-center justify-between gap-3">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total Due</span>
            <span class="text-lg font-black text-blue-600 dark:text-blue-400" x-text="currency + ' ' + grandTotal.toFixed(2)"></span>
        </div>
        <button type="submit" form="invoice-form" 
                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 active:scale-95 transition flex items-center gap-1.5">
            <i data-lucide="check" class="w-4 h-4"></i>
            <span>Save Invoice</span>
        </button>
    </div>

</div>
@endsection
