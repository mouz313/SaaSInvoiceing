@extends('layouts.app')

@section('title', 'Create Recurring Invoice')

@section('content')
<div class="max-w-5xl mx-auto space-y-8" 
     x-data="{
        taxRate: 0,
        discountRate: 0,
        currency: '{{ old('currency', Auth::user()->default_currency ?? 'USD') }}',
        selectedStyle: 'minimalist',
        selectedLogoId: {{ old('logo_id', 'null') }},
        logos: {{ json_encode($logos->map(fn($l) => ['id' => $l->id, 'filename' => $l->filename, 'url' => $l->url])) }},
        logoUploading: false,
        logoError: '',
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
        addProductToProfile(p) {
            this.items.push({
                description: p.name + (p.description ? ' — ' + p.description : ''),
                quantity: 1,
                unit_price: parseFloat(p.price) || 0
            });
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },
        items: [
            { description: 'Monthly Retainer Services', quantity: 1, unit_price: 1000.00 }
        ],
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0.00 });
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        additionalCharges: [],
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
            return this.subtotal * (parseFloat(this.discountRate) || 0) / 100;
        },
        get taxableAmount() {
            return this.subtotal - this.discountAmount;
        },
        get taxAmount() {
            return this.taxableAmount * (parseFloat(this.taxRate) || 0) / 100;
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
        get total() {
            return this.taxableAmount + this.taxAmount + this.additionalChargesTotal;
        }
     }">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"></i>
                Create Recurring Invoice Profile
            </h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">Automate recurring client billings, scheduled issues, and auto-email delivery</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('recurring.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
            <button type="submit" form="recurring-form" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition">
                <i data-lucide="check" class="w-4 h-4"></i>
                Save Recurring Profile
            </button>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm">
            <p class="font-bold mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="recurring-form" action="{{ route('recurring.store') }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="style" :value="selectedStyle">
        <input type="hidden" name="logo_id" :value="selectedLogoId">

        <!-- Recurring Schedule Details Card -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4"></i>
                1. Schedule &amp; Recurrence Rules
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Profile Title -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Profile / Retainer Title *</label>
                    <input type="text" name="title" value="{{ old('title', 'Monthly SEO & Maintenance Retainer') }}" required placeholder="e.g. Website Hosting, Monthly Consulting"
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none font-medium">
                </div>

                <!-- Frequency -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Repeat Frequency *</label>
                    <select name="frequency" required class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none font-medium">
                        <option value="weekly" {{ old('frequency') === 'weekly' ? 'selected' : '' }}>Weekly (Every 7 days)</option>
                        <option value="biweekly" {{ old('frequency') === 'biweekly' ? 'selected' : '' }}>Bi-Weekly (Every 14 days)</option>
                        <option value="monthly" {{ old('frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly (Default)</option>
                        <option value="quarterly" {{ old('frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly (Every 3 months)</option>
                        <option value="yearly" {{ old('frequency') === 'yearly' ? 'selected' : '' }}>Yearly (Annual renewal)</option>
                    </select>
                </div>

                <!-- Client Selection -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Bill To Client *</label>
                    <div class="flex gap-2">
                        <select name="client_id" required class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none font-medium">
                            <option value="">Select a client...</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->name }} {{ $client->company_name ? "({$client->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                        <a href="{{ route('clients.create') }}" target="_blank" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 hover:bg-slate-50 flex items-center justify-center">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Currency -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Currency *</label>
                    <select name="currency" x-model="currency" required class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none font-medium">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="CAD">CAD ($)</option>
                        <option value="AUD">AUD ($)</option>
                        <option value="PKR">PKR (Rs.)</option>
                        <option value="INR">INR (₹)</option>
                        <option value="AED">AED (AED)</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Start / First Issue Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">End Date (Optional)</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" placeholder="Leave blank for infinite"
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <span class="text-[10px] text-slate-400 mt-1 block">Leave empty to run indefinitely until paused</span>
                </div>

                <!-- Due Days -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Payment Terms (Due in Days) *</label>
                    <input type="number" name="due_days" value="{{ old('due_days', 14) }}" min="1" max="180" required
                           class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <!-- Auto-Send Email Toggle -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                <label class="relative flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="auto_send_email" value="1" {{ old('auto_send_email', true) ? 'checked' : '' }} class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i data-lucide="mail" class="w-4 h-4 text-indigo-600"></i>
                            Automatically Email Invoices to Client
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400 block mt-0.5">When active, newly generated invoices will be automatically dispatched to the client's email with the PDF attached</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- 1-Click Product Catalog Quick Add -->
        @if($products->count() > 0)
        <div class="p-6 rounded-2xl bg-gradient-to-br from-indigo-500/5 via-blue-500/5 to-transparent border border-indigo-100 dark:border-indigo-950/40 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-600 text-white"><i data-lucide="zap" class="w-4 h-4"></i></span>
                    <div>
                        <h4 class="text-xs font-bold text-indigo-950 dark:text-indigo-200 uppercase tracking-wider">⚡ 1-Click Product Picker</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Click any product from your catalog to populate line items instantly</p>
                    </div>
                </div>

                <!-- Category Tabs & Search -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 overflow-x-auto py-1">
                        <button type="button" @click="selectedCatalogCategory = 'all'"
                                :class="selectedCatalogCategory === 'all' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border border-slate-200 dark:border-slate-700 transition">
                            All
                        </button>
                        @foreach($categories as $cat)
                        <button type="button" @click="selectedCatalogCategory = '{{ $cat->id }}'"
                                :class="selectedCatalogCategory === '{{ $cat->id }}' ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100'"
                                class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border border-slate-200 dark:border-slate-700 transition whitespace-nowrap">
                            {{ $cat->name }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Product Cards Pill Container -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 max-h-48 overflow-y-auto pr-1">
                <template x-for="p in filteredProducts" :key="p.id">
                    <button type="button" @click="addProductToProfile(p)"
                            class="p-2.5 text-left rounded-xl bg-white dark:bg-slate-800/80 hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40 border border-slate-200 dark:border-slate-700/80 hover:border-indigo-300 dark:hover:border-indigo-800 transition group shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="text-[10px] font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider" x-text="p.category_name"></div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white truncate mt-0.5 group-hover:text-indigo-600 transition" x-text="p.name"></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-xs font-extrabold text-slate-900 dark:text-white" x-text="currency + ' ' + p.price.toFixed(2)"></span>
                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold group-hover:translate-x-0.5 transition">+ Add</span>
                        </div>
                    </button>
                </template>
            </div>
        </div>
        @endif

        <!-- Line Items Section -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    2. Invoice Items &amp; Pricing
                </h3>
                <button type="button" @click="addItem()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Add Line Item
                </button>
            </div>

            <!-- Items Table -->
            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <div class="flex-1 w-full">
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required placeholder="Item description or service details..."
                                   class="w-full px-3 py-2 rounded-lg text-xs sm:text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        <div class="w-full sm:w-28">
                            <input type="number" step="1" min="1" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" required placeholder="Qty"
                                   class="w-full px-3 py-2 rounded-lg text-xs sm:text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-right">
                        </div>
                        <div class="w-full sm:w-36">
                            <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" required placeholder="Unit Price"
                                   class="w-full px-3 py-2 rounded-lg text-xs sm:text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-right">
                        </div>
                        <div class="w-full sm:w-32 text-right font-bold text-xs sm:text-sm text-slate-900 dark:text-white py-2 px-1">
                            <span x-text="currency + ' ' + ((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0)).toFixed(2)"></span>
                        </div>
                        <button type="button" @click="removeItem(index)" class="p-2 text-slate-400 hover:text-rose-500 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Additional Charges Section -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                        Custom Charges &amp; Fees (Shipping, Setup Fee, Custom Taxes)
                    </span>
                    <button type="button" @click="addAdditionalCharge()" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Add Custom Charge
                    </button>
                </div>

                <template x-for="(charge, cIndex) in additionalCharges" :key="cIndex">
                    <div class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                        <input type="text" :name="'additional_charges[' + cIndex + '][name]'" x-model="charge.name" placeholder="Charge Name (e.g. Server Setup, Delivery)"
                               class="flex-1 px-3 py-1.5 rounded-lg text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                        <select :name="'additional_charges[' + cIndex + '][type]'" x-model="charge.type"
                                class="w-32 px-3 py-1.5 rounded-lg text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                        <input type="number" step="0.01" min="0" :name="'additional_charges[' + cIndex + '][value]'" x-model.number="charge.value" placeholder="Value"
                               class="w-28 px-3 py-1.5 rounded-lg text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-right">
                        <button type="button" @click="removeAdditionalCharge(cIndex)" class="p-1 text-slate-400 hover:text-rose-500">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Financial Summary Box -->
            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <div class="w-full sm:w-80 space-y-2.5 text-xs sm:text-sm">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-900 dark:text-white" x-text="currency + ' ' + subtotal.toFixed(2)"></span>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-600 dark:text-slate-400">Discount (%)</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.1" min="0" max="100" name="discount_rate" x-model.number="discountRate"
                                   class="w-20 px-2 py-1 rounded border border-slate-200 dark:border-slate-700 text-right text-xs bg-slate-50 dark:bg-slate-800">
                            <span class="font-semibold text-rose-500 w-24 text-right" x-text="'- ' + currency + ' ' + discountAmount.toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-slate-600 dark:text-slate-400">Tax (%)</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" step="0.1" min="0" max="100" name="tax_rate" x-model.number="taxRate"
                                   class="w-20 px-2 py-1 rounded border border-slate-200 dark:border-slate-700 text-right text-xs bg-slate-50 dark:bg-slate-800">
                            <span class="font-semibold text-slate-900 dark:text-white w-24 text-right" x-text="'+ ' + currency + ' ' + taxAmount.toFixed(2)"></span>
                        </div>
                    </div>

                    <template x-if="additionalChargesTotal > 0">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Additional Charges</span>
                            <span class="font-semibold text-slate-900 dark:text-white" x-text="'+ ' + currency + ' ' + additionalChargesTotal.toFixed(2)"></span>
                        </div>
                    </template>

                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <span class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Per-Invoice Total</span>
                        <span class="text-lg font-black text-indigo-600 dark:text-indigo-400" x-text="currency + ' ' + total.toFixed(2)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branding & Template Style -->
        <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
            <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-2">
                <i data-lucide="palette" class="w-4 h-4"></i>
                3. Branding &amp; Template
            </h3>

            <!-- Template Style Grid -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">Invoice Design Template</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <button type="button" @click="selectedStyle = 'minimalist'" 
                            :class="selectedStyle === 'minimalist' ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-indigo-50/20' : 'border-slate-200 dark:border-slate-700'"
                            class="p-4 rounded-xl border text-center transition hover:border-slate-400">
                        <div class="w-8 h-8 mx-auto rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 mb-2">
                            <i data-lucide="layout" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Minimalist</span>
                        <span class="text-[10px] text-slate-400">Clean &amp; Modern</span>
                    </button>
                    <button type="button" @click="selectedStyle = 'corporate'" 
                            :class="selectedStyle === 'corporate' ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-indigo-50/20' : 'border-slate-200 dark:border-slate-700'"
                            class="p-4 rounded-xl border text-center transition hover:border-slate-400">
                        <div class="w-8 h-8 mx-auto rounded-lg bg-blue-100 dark:bg-blue-950/50 flex items-center justify-center text-blue-600 dark:text-blue-400 mb-2">
                            <i data-lucide="briefcase" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Corporate</span>
                        <span class="text-[10px] text-slate-400">Navy Executive</span>
                    </button>
                    <button type="button" @click="selectedStyle = 'creative'" 
                            :class="selectedStyle === 'creative' ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-indigo-50/20' : 'border-slate-200 dark:border-slate-700'"
                            class="p-4 rounded-xl border text-center transition hover:border-slate-400">
                        <div class="w-8 h-8 mx-auto rounded-lg bg-purple-100 dark:bg-purple-950/50 flex items-center justify-center text-purple-600 dark:text-purple-400 mb-2">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Creative</span>
                        <span class="text-[10px] text-slate-400">Modern Gradient</span>
                    </button>
                    <button type="button" @click="selectedStyle = 'grid'" 
                            :class="selectedStyle === 'grid' ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-indigo-50/20' : 'border-slate-200 dark:border-slate-700'"
                            class="p-4 rounded-xl border text-center transition hover:border-slate-400">
                        <div class="w-8 h-8 mx-auto rounded-lg bg-emerald-100 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-2">
                            <i data-lucide="grid" class="w-4 h-4"></i>
                        </div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Grid</span>
                        <span class="text-[10px] text-slate-400">Structured Columns</span>
                    </button>
                </div>
            </div>

            <!-- Logo Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">Brand Logo</label>
                <div class="flex flex-wrap items-center gap-3">
                    <template x-for="logo in logos" :key="logo.id">
                        <div class="relative group">
                            <button type="button" @click="selectLogo(logo.id)"
                                    :class="selectedLogoId === logo.id ? 'border-indigo-600 ring-2 ring-indigo-500/20 bg-indigo-50/20' : 'border-slate-200 dark:border-slate-700'"
                                    class="w-20 h-16 rounded-xl border p-1.5 flex items-center justify-center transition overflow-hidden bg-white dark:bg-slate-800">
                                <img :src="logo.url" class="max-h-full max-w-full object-contain">
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Notes & Payment Instructions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-indigo-500"></i>
                    Default Notes to Client
                </h3>
                <textarea name="notes" rows="4" placeholder="Included in every generated invoice (e.g. Thank you for your continued partnership)..."
                          class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-3.5 h-3.5 text-indigo-500"></i>
                    Payment Instructions
                </h3>
                <textarea name="payment_instructions" rows="4" placeholder="Bank details, wire instructions or payment notes for client..."
                          class="w-full px-3.5 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('recurring.index') }}" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm transition">
                <i data-lucide="check" class="w-4 h-4"></i>
                Create Recurring Profile
            </button>
        </div>
    </form>
</div>
@endsection