@extends('layouts.app')

@section('title', 'Edit Invoice ' . $invoice->invoice_number)

@section('content')
<div class="max-w-5xl mx-auto space-y-8" 
     x-data="{
        taxRate: {{ $invoice->tax_rate }},
        discountRate: {{ $invoice->discount_rate }},
        currency: '{{ $invoice->currency }}',
        selectedStyle: '{{ $invoice->style }}',
        selectedLogoId: {{ $invoice->logo_id ?? 'null' }},
        logos: {{ json_encode($logos->map(fn($l) => ['id' => $l->id, 'filename' => $l->filename, 'url' => $l->url])) }},
        logoUploading: false,
        logoError: '',
        previewModalOpen: false,
        previewSlug: '',
        previewName: '',
        previewCategory: '',
        previewPrice: 0,
        previewIsOwned: false,
        openPreviewModal(slug, name, category, price, isOwned) {
            this.previewSlug = slug;
            this.previewName = name;
            this.previewCategory = category;
            this.previewPrice = price;
            this.previewIsOwned = isOwned;
            this.previewModalOpen = true;
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
        items: {{ json_encode($invoice->items->map(fn($i) => ['description' => $i->description, 'quantity' => (float)$i->quantity, 'unit_price' => (float)$i->unit_price])) }},
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0.00 });
            this.$nextTick(() => { window.reinitIcons && window.reinitIcons(); });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        additionalCharges: {{ json_encode($invoice->additional_charges ?? []) }},
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

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Edit Invoice #{{ $invoice->invoice_number }}</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Modify client, line items, or layout template</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 transition">
                Cancel
            </a>
            <button type="submit" form="invoice-edit-form" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-600/20 transition">
                <i data-lucide="check" class="w-4 h-4"></i>
                Update Invoice
            </button>
        </div>
    </div>

    <form id="invoice-edit-form" method="POST" action="{{ route('invoices.update', $invoice) }}" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 border-b border-slate-100 dark:border-slate-800 pb-2">
                General Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Client *
                    </label>
                    <select name="client_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ $invoice->client_id == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} {{ $client->company_name ? "({$client->company_name})" : "" }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Invoice Number *
                    </label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

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
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Issue Date</label>
                    <input type="date" name="invoice_date" value="{{ $invoice->invoice_date->format('Y-m-d') }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Due Date</label>
                    <input type="date" name="due_date" value="{{ $invoice->due_date->format('Y-m-d') }}" required
                           class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select name="status" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-600 focus:outline-none">
                        <option value="draft" {{ $invoice->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ $invoice->status === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ $invoice->status === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ $invoice->status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Company Logo Gallery -->
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

            <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-8 gap-3">
                <template x-for="logo in logos" :key="logo.id">
                    <div @click="selectLogo(logo.id)"
                         :class="selectedLogoId === logo.id
                            ? 'ring-2 ring-indigo-600 border-transparent bg-indigo-50 dark:bg-indigo-950/20'
                            : 'border-slate-200 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500'"
                         class="relative group cursor-pointer rounded-xl border p-2 flex items-center justify-center aspect-square transition overflow-hidden">
                        <img :src="logo.url" :alt="logo.filename" class="max-h-full max-w-full object-contain">
                        <div x-show="selectedLogoId === logo.id"
                             class="absolute top-1 left-1 w-4 h-4 bg-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <button type="button" @click="deleteLogo(logo, $event)"
                                class="absolute top-1 right-1 w-5 h-5 bg-rose-600 hover:bg-rose-700 text-white rounded-full items-center justify-center opacity-0 group-hover:opacity-100 transition hidden sm:flex">
                            <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </template>

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

            <p x-show="logoError" x-text="logoError" class="text-xs text-rose-600 dark:text-rose-400"></p>
            <p x-show="selectedLogoId" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                ✓ Logo selected — it will appear in your invoice header. Click again to deselect.
            </p>
            <p x-show="!selectedLogoId && logos.length > 0" class="text-xs text-slate-400 dark:text-slate-500">
                Click a logo to select it, or leave unselected for no logo.
            </p>
        </div>

        <!-- Style Selector -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Invoice Style Template (25 Themes)
                </h3>
                <a href="{{ route('templates.index') }}" target="_blank" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i> Visit Template Store
                </a>
            </div>
            <input type="hidden" name="style" :value="selectedStyle">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 max-h-72 overflow-y-auto pr-1">
                @foreach($templates as $tmpl)
                    @php
                        $isOwned = in_array($tmpl->slug, $ownedSlugs ?? [], true) || $tmpl->slug === $invoice->style;
                    @endphp
                    @if($isOwned)
                        <div @click="selectedStyle = '{{ $tmpl->slug }}'" 
                             :class="selectedStyle === '{{ $tmpl->slug }}' ? 'ring-2 ring-blue-600 border-transparent bg-blue-50/40 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'" 
                             class="cursor-pointer rounded-xl border p-3.5 transition flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $tmpl->category }}</span>
                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-0.5">
                                        <i data-lucide="check" class="w-3 h-3"></i> Ready
                                    </span>
                                </div>
                                <h4 class="font-bold text-sm text-slate-900 dark:text-white">{{ $tmpl->name }}</h4>
                                <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $tmpl->description }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                                <span class="text-xs font-bold text-blue-600 flex items-center gap-1" x-show="selectedStyle === '{{ $tmpl->slug }}'">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Selected
                                </span>
                                <span class="text-slate-400 font-semibold" x-show="selectedStyle !== '{{ $tmpl->slug }}'">
                                    Click to use
                                </span>
                                <button type="button" 
                                        @click.stop="openPreviewModal('{{ $tmpl->slug }}', '{{ addslashes($tmpl->name) }}', '{{ $tmpl->category }}', {{ $tmpl->price }}, true)" 
                                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-[11px] font-medium flex items-center gap-0.5 cursor-pointer">
                                    <i data-lucide="eye" class="w-3 h-3"></i> Preview
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/30 p-3.5 transition flex flex-col justify-between opacity-80 hover:opacity-100">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $tmpl->category }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 flex items-center gap-1">
                                        <i data-lucide="lock" class="w-2.5 h-2.5"></i> ${{ number_format($tmpl->price, 0) }}
                                    </span>
                                </div>
                                <h4 class="font-bold text-sm text-slate-700 dark:text-slate-300">{{ $tmpl->name }}</h4>
                                <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $tmpl->description }}</p>
                            </div>
                            <div class="mt-2.5 pt-2 border-t border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between text-xs">
                                <button type="button" 
                                        @click.stop="openPreviewModal('{{ $tmpl->slug }}', '{{ addslashes($tmpl->name) }}', '{{ $tmpl->category }}', {{ $tmpl->price }}, false)" 
                                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-[11px] font-medium flex items-center gap-0.5 cursor-pointer">
                                    <i data-lucide="eye" class="w-3 h-3"></i> Preview
                                </button>
                                <a href="{{ route('templates.index') }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 font-bold text-[11px] hover:underline flex items-center gap-0.5">
                                    <i data-lucide="shopping-bag" class="w-3 h-3"></i> Unlock
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- ================= TEMPLATE PREVIEW MODAL (Zero Iframe) ================= -->
        <div x-show="previewModalOpen" 
             x-cloak
             style="display: none; z-index: 99999;" 
             class="fixed inset-0 overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-2 sm:p-4 md:p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="previewModalOpen = false"
             @keydown.escape.window="previewModalOpen = false">

            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-5xl w-full max-h-[92vh] shadow-2xl flex flex-col overflow-hidden border border-slate-200 dark:border-slate-800 relative z-10">
                
                <!-- Modal Top Bar -->
                <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4 bg-slate-50/90 dark:bg-slate-900/90 shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-blue-600/10 text-blue-600 flex items-center justify-center shrink-0">
                            <i data-lucide="layout-template" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-black text-slate-900 dark:text-white truncate" x-text="previewName"></h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300" x-text="previewCategory"></span>
                                
                                <template x-if="previewIsOwned">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 flex items-center gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i> Unlocked
                                    </span>
                                </template>
                                <template x-if="!previewIsOwned">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                        $<span x-text="Number(previewPrice).toFixed(2)"></span>
                                    </span>
                                </template>
                            </div>
                            <p class="text-xs text-slate-400 truncate">Protected Interactive Preview • Watermarked design sample</p>
                        </div>
                    </div>

                    <!-- Action buttons & Close -->
                    <div class="flex items-center gap-2.5 shrink-0">
                        <template x-if="previewIsOwned">
                            <button type="button" 
                                    @click="selectedStyle = previewSlug; previewModalOpen = false;"
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                <span>Apply This Template</span>
                            </button>
                        </template>

                        <template x-if="!previewIsOwned">
                            <a href="{{ route('templates.index') }}" target="_blank"
                               class="px-4 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-1.5">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                <span>Unlock in Store</span>
                            </a>
                        </template>

                        <button type="button" @click="previewModalOpen = false" 
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body (Zero Iframe, Instant Render, Protected) -->
                <div class="flex-1 bg-slate-100 dark:bg-slate-950 p-3 sm:p-6 overflow-y-auto max-h-[calc(92vh-75px)] flex justify-center">
                    <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 w-full max-w-4xl overflow-hidden self-start select-none"
                         style="user-select: none; -webkit-user-select: none;">
                        
                        <!-- High-Density Diagonal SVG Watermark Overlay (Only if not owned) -->
                        <template x-if="!previewIsOwned">
                            <div class="absolute inset-0 z-30 pointer-events-none overflow-hidden opacity-30 select-none">
                                <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <pattern id="wmPatternModalEdit" width="280" height="180" patternUnits="userSpaceOnUse" patternTransform="rotate(-30)">
                                            <text x="20" y="40" font-family="'Helvetica Neue', Arial, sans-serif" font-weight="900" font-size="12" fill="#0f172a" letter-spacing="3" text-transform="uppercase">
                                                PREVIEW ONLY • DO NOT COPY
                                            </text>
                                            <text x="40" y="110" font-family="'Helvetica Neue', Arial, sans-serif" font-weight="800" font-size="10" fill="#2563eb" letter-spacing="2">
                                                INVOICEHUB SAMPLE
                                            </text>
                                            <text x="10" y="160" font-family="'Helvetica Neue', Arial, sans-serif" font-weight="900" font-size="11" fill="#dc2626" letter-spacing="3">
                                                PURCHASE TO UNLOCK
                                            </text>
                                        </pattern>
                                    </defs>
                                    <rect width="100%" height="100%" fill="url(#wmPatternModalEdit)" />
                                </svg>
                            </div>
                        </template>

                        <!-- Transparent Protective Glass Shield -->
                        <template x-if="!previewIsOwned">
                            <div class="absolute inset-0 z-20 pointer-events-auto bg-transparent cursor-default"
                                 @contextmenu.prevent
                                 @dragstart.prevent
                                 title="Protected Preview"></div>
                        </template>

                        <!-- Direct Instant Template Inclusions -->
                        <div class="relative z-10 pointer-events-none p-2 sm:p-4">
                            @foreach($templates as $tmpl)
                                <div x-show="previewSlug === '{{ $tmpl->slug }}'" x-cloak>
                                    @include('invoices.templates.' . $tmpl->slug, ['invoice' => $mockInvoice, 'isPdf' => false])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Line Items
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
                            <input type="text" :name="'items[' + index + '][description]'" x-model="item.description" required
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none">
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <input type="number" step="1" min="1" :name="'items[' + index + '][quantity]'" x-model="item.quantity" required
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-right focus:outline-none">
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <input type="number" step="any" min="0" :name="'items[' + index + '][unit_price]'" x-model="item.unit_price" required
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-right focus:outline-none">
                        </div>
                        <div class="col-span-3 sm:col-span-1 text-right font-bold text-xs sm:text-sm">
                            <span x-text="currency + ' ' + (((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_price) || 0))).toFixed(2)"></span>
                        </div>
                        <div class="col-span-1 text-right">
                            <button type="button" @click="removeItem(index)" :disabled="items.length === 1" 
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 transition disabled:opacity-30">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" @click="addItem()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-dashed border-blue-400 text-blue-600 text-xs font-bold hover:bg-blue-50">
                <i data-lucide="plus" class="w-4 h-4"></i> Add Line Item
            </button>
        </div>

        <!-- Custom Taxes & Additional Charges -->
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
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label class="block sm:hidden text-[10px] font-bold uppercase text-slate-400 mb-1">Type</label>
                            <select :name="'additional_charges[' + cIdx + '][type]'" x-model="charge.type" 
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:outline-none">
                                <option value="fixed">Fixed Amount ($)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <label class="block sm:hidden text-[10px] font-bold uppercase text-slate-400 mb-1">Value</label>
                            <input type="number" step="any" min="0" :name="'additional_charges[' + cIdx + '][value]'" x-model="charge.value" required placeholder="0.00"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm text-right focus:outline-none">
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

        <!-- Calculations & Submit -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Bank Instructions</label>
                    <textarea name="payment_instructions" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">{{ $invoice->payment_instructions }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">{{ $invoice->notes }}</textarea>
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400">Subtotal</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="currency + ' ' + subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400">Discount (%)</span>
                        <input type="number" step="any" min="0" max="100" name="discount_rate" x-model="discountRate" class="w-16 px-2 py-1 rounded border text-right text-xs">
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400">Tax (%)</span>
                        <input type="number" step="any" min="0" max="100" name="tax_rate" x-model="taxRate" class="w-16 px-2 py-1 rounded border text-right text-xs">
                    </div>

                    <!-- Additional Charges in Breakdown -->
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

                <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md">
                    Update Invoice
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
