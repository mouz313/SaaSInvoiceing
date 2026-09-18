@extends('layouts.app')

@section('title', 'Create Invoice')

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
        addProductToInvoice(p) {
            this.items.push({
                description: p.name + (p.description ? ' — ' + p.description : ''),
                quantity: 1,
                unit_price: parseFloat(p.price) || 0
            });
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },
        items: [
            { description: 'Web Design & Development Services', quantity: 1, unit_price: 1200.00 }
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

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Invoice Builder</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Configure line items, automatic taxes, and select invoice style</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
            <button type="submit" form="invoice-form" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-600/20 transition">
                <i data-lucide="check" class="w-4 h-4"></i>
                Save & Generate Invoice
            </button>
        </div>
    </div>

    @if($clients->isEmpty())
    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 flex items-center justify-between text-sm">
        <div class="flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 shrink-0"></i>
            <span>You haven't added any clients yet. You must add a client profile before issuing an invoice.</span>
        </div>
        <a href="{{ route('clients.create') }}" class="px-3 py-1.5 rounded-lg bg-amber-600 text-white font-bold text-xs hover:bg-amber-700">
            + Add Client Now
        </a>
    </div>
    @endif

    <form id="invoice-form" method="POST" action="{{ route('invoices.store') }}" class="space-y-8">
        @csrf

        <!-- Section 1: Client & Invoice Metadata -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 pb-2">
                General Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Client Selector -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                            Client *
                        </label>
                        <a href="{{ route('clients.create') }}" class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold hover:underline">
                            + New Client
                        </a>
                    </div>
                    <select name="client_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="">Select client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} {{ $client->company_name ? "({$client->company_name})" : "" }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Invoice Number -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Invoice Number *
                    </label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $defaultNumber) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                    @error('invoice_number') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Currency -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Currency *
                    </label>
                    <select name="currency" x-model="currency" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="CAD">CAD ($)</option>
                        <option value="AUD">AUD ($)</option>
                        <option value="PKR">PKR (₨)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Issue Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Issue Date *
                    </label>
                    <input type="date" name="invoice_date" value="{{ old('invoice_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Due Date *
                    </label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d', strtotime('+14 days'))) }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <!-- Initial Status -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Status *
                    </label>
                    <select name="status" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="draft" selected>Draft</option>
                        <option value="sent">Sent</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 1.5: Company Logo Gallery -->
        <input type="hidden" name="logo_id" :value="selectedLogoId">
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Company Logo <span class="text-indigo-500">✦</span>
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Upload your logo once, reuse across all invoices</p>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full"
                    :class="logoCount >= maxLogos ? 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'"
                    x-text="logoCount + ' / ' + maxLogos + ' logos'"></span>
            </div>

            <!-- Logo Thumbnails Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-8 gap-3">
                <template x-for="logo in logos" :key="logo.id">
                    <div @click="selectLogo(logo.id)"
                         :class="selectedLogoId === logo.id
                            ? 'ring-2 ring-indigo-600 border-transparent bg-indigo-50 dark:bg-indigo-950/20'
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500'"
                         class="relative group cursor-pointer rounded-xl border p-2 flex items-center justify-center aspect-square transition overflow-hidden">
                        <img :src="logo.url" :alt="logo.filename" class="max-h-full max-w-full object-contain">
                        <!-- Selected tick -->
                        <div x-show="selectedLogoId === logo.id"
                             class="absolute top-1 left-1 w-4 h-4 bg-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <!-- Delete button -->
                        <button type="button" @click="deleteLogo(logo, $event)"
                                class="absolute top-1 right-1 w-5 h-5 bg-rose-600 hover:bg-rose-700 text-white rounded-full items-center justify-center opacity-0 group-hover:opacity-100 transition hidden sm:flex">
                            <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <p class="sr-only" x-text="logo.filename"></p>
                    </div>
                </template>

                <!-- Upload new logo button -->
                <label x-show="logoCount < maxLogos"
                       :class="logoUploading ? 'opacity-60 cursor-wait' : 'cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-500'"
                       class="rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-600 flex flex-col items-center justify-center aspect-square transition gap-1 text-slate-400 dark:text-slate-500">
                    <template x-if="!logoUploading">
                        <div class="flex flex-col items-center gap-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span class="text-[10px] font-semibold uppercase tracking-wider">Upload</span>
                        </div>
                    </template>
                    <template x-if="logoUploading">
                        <svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <input type="file" class="sr-only" accept="image/jpeg,image/png,image/jpg,image/webp,image/svg+xml"
                           @change="uploadLogo($event)" :disabled="logoUploading">
                </label>
            </div>

            <!-- Error message -->
            <p x-show="logoError" x-text="logoError" class="text-xs text-rose-600 dark:text-rose-400"></p>

            <!-- Selected indicator -->
            <p x-show="selectedLogoId" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                ✓ Logo selected — it will appear in your invoice header. Click the logo again to deselect.
            </p>
            <p x-show="!selectedLogoId && logos.length > 0" class="text-xs text-slate-400 dark:text-slate-500">
                Click a logo to select it for this invoice, or leave unselected for no logo.
            </p>
        </div>

        <!-- Section 2: Phase 6 Style Selector 🎨 -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Invoice Style Selector 🎨
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Choose the layout that matches your client's aesthetic</p>
                </div>
                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 capitalize" x-text="'Selected: ' + selectedStyle"></span>
            </div>

            <input type="hidden" name="style" :value="selectedStyle">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Style 1: Modern Minimalist -->
                <div @click="selectedStyle = 'minimalist'" 
                     :class="selectedStyle === 'minimalist' ? 'ring-2 ring-blue-600 border-transparent bg-blue-50/40 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                     class="cursor-pointer rounded-xl border p-4 transition text-left flex flex-col justify-between">
                    <div>
                        <div class="h-24 rounded-lg bg-slate-100 dark:bg-slate-800 flex flex-col justify-between p-2.5 border border-slate-200/60 dark:border-slate-700/60 mb-3">
                            <div class="w-12 h-2 rounded-sm bg-slate-900 dark:bg-white"></div>
                            <div class="space-y-1">
                                <div class="w-full h-1.5 rounded-sm bg-slate-300 dark:bg-slate-700"></div>
                                <div class="w-2/3 h-1.5 rounded-sm bg-slate-300 dark:bg-slate-700"></div>
                            </div>
                            <div class="w-8 h-2 rounded-sm bg-blue-600 self-end"></div>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Modern Minimalist</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Monochrome, high whitespace, clean typography.</p>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-blue-600" x-show="selectedStyle === 'minimalist'">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Active Template
                    </div>
                </div>

                <!-- Style 2: Corporate Classic -->
                <div @click="selectedStyle = 'corporate'" 
                     :class="selectedStyle === 'corporate' ? 'ring-2 ring-blue-600 border-transparent bg-blue-50/40 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                     class="cursor-pointer rounded-xl border p-4 transition text-left flex flex-col justify-between">
                    <div>
                        <div class="h-24 rounded-lg bg-slate-100 dark:bg-slate-800 flex flex-col justify-between p-2.5 border border-slate-200/60 dark:border-slate-700/60 mb-3">
                            <div class="w-full h-4 rounded-sm bg-slate-900 text-white text-[8px] flex items-center px-1 font-bold">OFFICIAL</div>
                            <div class="space-y-1">
                                <div class="w-full h-1.5 rounded-sm bg-slate-300 dark:bg-slate-700"></div>
                                <div class="w-full h-1.5 rounded-sm bg-slate-300 dark:bg-slate-700"></div>
                            </div>
                            <div class="w-16 h-2 rounded-sm bg-slate-400"></div>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Corporate Classic</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Deep navy header bar, formal borders, executive signatures.</p>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-blue-600" x-show="selectedStyle === 'corporate'">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Active Template
                    </div>
                </div>

                <!-- Style 3: Creative Bold -->
                <div @click="selectedStyle = 'creative'" 
                     :class="selectedStyle === 'creative' ? 'ring-2 ring-blue-600 border-transparent bg-blue-50/40 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                     class="cursor-pointer rounded-xl border p-4 transition text-left flex flex-col justify-between">
                    <div>
                        <div class="h-24 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 flex flex-col justify-between p-2.5 text-white mb-3 shadow-xs">
                            <div class="flex justify-between items-center">
                                <div class="w-8 h-2 rounded-full bg-white/40"></div>
                                <div class="w-6 h-2 rounded-full bg-white"></div>
                            </div>
                            <div class="w-full h-2 rounded-sm bg-white/20"></div>
                            <div class="w-10 h-3 rounded-full bg-white/30 self-end"></div>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Creative Bold</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Vibrant gradient banner, carded line items, modern pills.</p>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-blue-600" x-show="selectedStyle === 'creative'">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Active Template
                    </div>
                </div>

                <!-- Style 4: Clean Grid -->
                <div @click="selectedStyle = 'grid'" 
                     :class="selectedStyle === 'grid' ? 'ring-2 ring-blue-600 border-transparent bg-blue-50/40 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'"
                     class="cursor-pointer rounded-xl border p-4 transition text-left flex flex-col justify-between">
                    <div>
                        <div class="h-24 rounded-lg bg-slate-100 dark:bg-slate-800 border-2 border-slate-900 dark:border-slate-600 p-2 flex flex-col justify-between mb-3">
                            <div class="grid grid-cols-2 gap-1 border-b border-slate-400 pb-1">
                                <div class="h-2 bg-slate-900 dark:bg-slate-400"></div>
                                <div class="h-2 bg-slate-300 dark:bg-slate-700"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <div class="h-1.5 bg-slate-300 dark:bg-slate-600"></div>
                                <div class="h-1.5 bg-slate-300 dark:bg-slate-600"></div>
                                <div class="h-1.5 bg-slate-300 dark:bg-slate-600"></div>
                            </div>
                            <div class="h-3 bg-slate-900 dark:bg-slate-400 text-[6px] text-white flex items-center justify-center font-bold">GRID TOTAL</div>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">Clean Grid</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Structured boxed layout, monospace numbers, tech billing.</p>
                    </div>
                    <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-blue-600" x-show="selectedStyle === 'grid'">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i> Active Template
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Dynamic Line Items -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Line Items & Deliverables
                </h3>
                <span class="text-xs font-semibold text-slate-500" x-text="items.length + ' item(s)'"></span>
            </div>

            <!-- ⚡ Quick-Add from Products Catalog (Category Tabs) -->
            <div class="p-4 rounded-xl bg-blue-50/50 dark:bg-blue-950/20 border border-blue-200/60 dark:border-blue-800/50 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-900 dark:text-blue-300 flex items-center gap-1.5">
                            <i data-lucide="zap" class="w-3.5 h-3.5 text-amber-500"></i>
                            1-Click Add From Products Catalog
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="catalogSearch" placeholder="Search catalog items..." 
                               class="text-xs px-2.5 py-1 rounded-lg border border-blue-200 dark:border-blue-900 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <a href="{{ route('products.create') }}" target="_blank" class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                            <i data-lucide="plus" class="w-3 h-3"></i> Add Product
                        </a>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                    <button type="button" @click="selectedCatalogCategory = 'all'" 
                            :class="selectedCatalogCategory === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                            class="px-3 py-1 rounded-lg text-xs font-semibold whitespace-nowrap transition">
                        All Items (<span x-text="products.length"></span>)
                    </button>
                    @foreach($categories as $category)
                        <button type="button" @click="selectedCatalogCategory = '{{ $category->id }}'" 
                                :class="selectedCatalogCategory === '{{ $category->id }}' ? 'bg-blue-600 text-white shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                class="px-3 py-1 rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center gap-1">
                            <span>{{ $category->name }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Product Chips Grid -->
                <div class="max-h-48 overflow-y-auto">
                    <template x-if="filteredProducts.length === 0">
                        <p class="text-xs text-slate-400 italic py-2">No catalog items in this category. Create products in the Catalog tab to add with 1 click.</p>
                    </template>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        <template x-for="prod in filteredProducts" :key="prod.id">
                            <button type="button" @click="addProductToInvoice(prod)" 
                                    class="text-left p-2.5 rounded-xl bg-white dark:bg-slate-850 hover:bg-blue-50 dark:hover:bg-blue-900/30 border border-slate-200/80 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-600 transition flex items-center justify-between group shadow-xs">
                                <div class="min-w-0 flex-1 mr-2">
                                    <div class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate group-hover:text-blue-600 dark:group-hover:text-blue-400" x-text="prod.name"></div>
                                    <div class="text-[10px] text-slate-400 truncate" x-text="prod.category_name + ' • ' + currency + ' ' + parseFloat(prod.price).toFixed(2)"></div>
                                </div>
                                <span class="px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 text-[11px] font-bold group-hover:bg-blue-600 group-hover:text-white transition shrink-0 flex items-center gap-0.5">
                                    <i data-lucide="plus" class="w-3 h-3"></i> Add
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-12 gap-3 items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60">
                        <div class="col-span-12 sm:col-span-6">
                            <label class="block sm:hidden text-[10px] font-bold uppercase text-slate-400 mb-1">Description</label>
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required placeholder="Item / Service description"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block sm:hidden text-[10px] font-bold uppercase text-slate-400 mb-1">Qty</label>
                            <input type="number" step="1" min="1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required placeholder="1"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-right focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block sm:hidden text-[10px] font-bold uppercase text-slate-400 mb-1">Price</label>
                            <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required placeholder="0.00"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-right focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                        <div class="col-span-3 sm:col-span-1 text-right font-bold text-xs sm:text-sm text-slate-800 dark:text-slate-200">
                            <span x-text="currency + ' ' + (((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0))).toFixed(2)"></span>
                        </div>
                        <div class="col-span-1 text-right">
                            <button type="button" @click="removeItem(index)" :disabled="items.length === 1" 
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition disabled:opacity-30 disabled:cursor-not-allowed">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" @click="addItem()" 
                    class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl border-2 border-dashed border-blue-400 dark:border-blue-700 text-blue-600 dark:text-blue-400 text-xs font-bold hover:bg-blue-50 dark:hover:bg-blue-950/40 transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Custom Line Item
            </button>
        </div>

        <!-- Section 4: Custom Taxes & Additional Charges -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        Custom Taxes & Additional Charges
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Add shipping, handling fees, packaging, provincial tax, or service charges</p>
                </div>
                <button type="button" @click="addAdditionalCharge()" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 hover:bg-blue-100 font-semibold text-xs transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Add Charge / Tax
                </button>
            </div>

            <template x-if="additionalCharges.length === 0">
                <div class="text-center py-4 text-xs text-slate-400">
                    No custom taxes or additional fees added. Click "+ Add Charge / Tax" to add shipping, service fee, etc.
                </div>
            </template>

            <div class="space-y-3" x-show="additionalCharges.length > 0">
                <template x-for="(charge, cIdx) in additionalCharges" :key="cIdx">
                    <div class="grid grid-cols-12 gap-3 items-center p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60">
                        <div class="col-span-12 sm:col-span-5">
                            <label class="block sm:hidden text-[10px] font-bold uppercase text-slate-400 mb-1">Fee / Tax Name</label>
                            <input type="text" :name="'additional_charges[' + cIdx + '][name]'" x-model="charge.name" placeholder="e.g. Shipping, Delivery, Service Fee" required
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label class="block sm:hidden text-[10px] font-bold uppercase text-slate-400 mb-1">Type</label>
                            <select :name="'additional_charges[' + cIdx + '][type]'" x-model="charge.type" 
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                                <option value="fixed">Fixed Amount ($)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block sm:hidden text-[10px] font-bold uppercase text-slate-400 mb-1">Value</label>
                            <input type="number" step="any" min="0" :name="'additional_charges[' + cIdx + '][value]'" x-model="charge.value" required placeholder="0.00"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-right focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        </div>
                        <div class="col-span-1 text-right font-bold text-xs text-slate-700 dark:text-slate-300">
                            <span x-text="'+' + (charge.type === 'percentage' ? (taxableAmount * ((parseFloat(charge.value) || 0) / 100)).toFixed(2) : (parseFloat(charge.value) || 0).toFixed(2))"></span>
                        </div>
                        <div class="col-span-1 text-right">
                            <button type="button" @click="removeAdditionalCharge(cIdx)" 
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Section 3: Notes & Totals -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left: Notes & Bank Info -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs space-y-4">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-2">Notes & Instructions</h3>
                
                <div>
                    <label for="payment_instructions" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Bank / Remittance Instructions</label>
                    <textarea name="payment_instructions" id="payment_instructions" rows="3" placeholder="Wire transfer instructions, bank name, account number, routing..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('payment_instructions', Auth::user()->default_payment_instructions) }}</textarea>
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Invoice Notes / Terms</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Thank you for your business! Payment is due within 14 days..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">{{ old('notes', Auth::user()->default_notes) }}</textarea>
                </div>
            </div>

            <!-- Right: Calculation Breakdown -->
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-xs flex flex-col justify-between space-y-6">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Summary & Totals</h3>

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

                    <!-- Dynamic Additional Charges Breakdown -->
                    <template x-for="(charge, cIdx) in additionalCharges" :key="'sum-' + cIdx">
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400" x-show="charge.name">
                            <span x-text="charge.name + (charge.type === 'percentage' ? ' (' + charge.value + '%)' : '')"></span>
                            <span class="font-semibold text-slate-900 dark:text-white" 
                                  x-text="'+' + currency + ' ' + (charge.type === 'percentage' ? (taxableAmount * ((parseFloat(charge.value) || 0) / 100)).toFixed(2) : (parseFloat(charge.value) || 0).toFixed(2))"></span>
                        </div>
                    </template>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center">
                        <span class="text-base font-extrabold text-slate-900 dark:text-white">Grand Total</span>
                        <span class="text-2xl font-extrabold text-blue-600 dark:text-blue-400" x-text="currency + ' ' + grandTotal.toFixed(2)"></span>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-600/20 transition">
                    Save Invoice & Generate PDF
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
