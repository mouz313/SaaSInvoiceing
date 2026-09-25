@extends('layouts.app')

@section('title', 'Invoice Template Gallery & Store')

@section('content')
@php
    $initialPreviewSlug = request('preview');
    $initialTemplate = $initialPreviewSlug ? $templates->firstWhere('slug', $initialPreviewSlug) : null;
    $initialIsOwned = $initialTemplate ? in_array($initialTemplate->slug, $ownedSlugs, true) : false;
@endphp

<div class="space-y-8 max-w-7xl mx-auto pb-16"
     x-data="{
         previewModalOpen: {{ $initialTemplate ? 'true' : 'false' }},
         templateSlug: '{{ $initialTemplate ? $initialTemplate->slug : '' }}',
         templateName: '{{ $initialTemplate ? addslashes($initialTemplate->name) : '' }}',
         templateCategory: '{{ $initialTemplate ? $initialTemplate->category : '' }}',
         templatePrice: {{ $initialTemplate ? $initialTemplate->price : 0 }},
         templateIsFree: {{ $initialTemplate && $initialTemplate->is_free ? 'true' : 'false' }},
         templateIsOwned: {{ $initialIsOwned ? 'true' : 'false' }},
         openPreview(slug, name, category, price, isFree, isOwned) {
             this.templateSlug = slug;
             this.templateName = name;
             this.templateCategory = category;
             this.templatePrice = price;
             this.templateIsFree = Boolean(isFree);
             this.templateIsOwned = Boolean(isOwned);
             this.previewModalOpen = true;
         }
     }"
     @keydown.escape.window="previewModalOpen = false">

    <!-- Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-8 sm:p-10 text-white shadow-2xl border border-slate-800">
        <div class="absolute -right-16 -top-16 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold uppercase tracking-wider mb-4 border border-indigo-500/30">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    25 Curated Aesthetic Designs
                </div>
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white">
                    Invoice Template Store
                </h1>
                <p class="mt-3 text-sm sm:text-base text-slate-300 leading-relaxed">
                    Elevate your business brand with 25 distinct modern invoice themes. Switch styles anytime, export pixel-perfect PDFs, and impress your clients.
                </p>
                <div class="mt-4 flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-300">
                    <span class="flex items-center gap-1.5"><i data-lucide="shield-check" class="w-4 h-4 text-emerald-400"></i> Free Protected Previews</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="zap" class="w-4 h-4 text-amber-400"></i> One-time Unlock for Lifetime Use</span>
                    <span class="flex items-center gap-1.5"><i data-lucide="printer" class="w-4 h-4 text-sky-400"></i> High-Resolution DomPDF Engine</span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row md:flex-col gap-3 shrink-0">
                <a href="{{ route('invoices.create') }}" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-bold shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Create New Invoice
                </a>
                <div class="p-3 rounded-2xl bg-white/5 border border-white/10 text-center">
                    <p class="text-xs text-slate-400 font-medium">Your Unlocked Themes</p>
                    <p class="text-xl font-black text-white mt-0.5">{{ count($ownedSlugs) }} / {{ \App\Models\InvoiceTemplate::count() }} Available</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white dark:bg-slate-850 rounded-2xl p-5 shadow-sm border border-slate-200/80 dark:border-slate-800 space-y-4">
        <form method="GET" action="{{ route('templates.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
            <!-- Search -->
            <div class="relative flex-1 max-w-md">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Search designs (e.g., Swiss, Dark, Minimal, Bold)..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <!-- Ownership Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                <a href="{{ route('templates.index', ['filter' => 'all', 'category' => $category, 'search' => $search]) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filter === 'all' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    All Templates ({{ \App\Models\InvoiceTemplate::where('is_active', true)->count() }})
                </a>
                <a href="{{ route('templates.index', ['filter' => 'owned', 'category' => $category, 'search' => $search]) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filter === 'owned' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    ✓ Unlocked ({{ count($ownedSlugs) }})
                </a>
                <a href="{{ route('templates.index', ['filter' => 'premium', 'category' => $category, 'search' => $search]) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filter === 'premium' ? 'bg-purple-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    ⭐ Premium Store
                </a>
                <a href="{{ route('templates.index', ['filter' => 'free', 'category' => $category, 'search' => $search]) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $filter === 'free' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    Free Core
                </a>
            </div>
        </form>

        <!-- Category Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
            <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px] shrink-0 mr-1">Category:</span>
            <a href="{{ route('templates.index', ['filter' => $filter, 'category' => 'all', 'search' => $search]) }}"
               class="px-3 py-1 rounded-lg font-semibold transition {{ empty($category) || $category === 'all' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                All
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('templates.index', ['filter' => $filter, 'category' => $cat, 'search' => $search]) }}"
                   class="px-3 py-1 rounded-lg font-semibold transition whitespace-nowrap {{ $category === $cat ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Template Grid (25 Designs) -->
    @if($templates->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center bg-white/50 dark:bg-slate-850/50">
            <i data-lucide="layout-grid" class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3"></i>
            <h3 class="text-base font-bold text-slate-700 dark:text-slate-300">No templates found</h3>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Try clearing your search keyword or switching the category filter.</p>
            <a href="{{ route('templates.index') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold hover:bg-blue-700 transition">
                Reset Filters
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($templates as $template)
                @php
                    $isOwned = in_array($template->slug, $ownedSlugs, true);
                @endphp
                <div class="group bg-white dark:bg-slate-850 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:shadow-xl hover:border-blue-500/40 dark:hover:border-blue-500/40 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                    
                    <!-- Card Thumbnail Visual Preview -->
                    <div class="relative bg-slate-100 dark:bg-slate-900/80 p-5 aspect-4/3 flex flex-col justify-between border-b border-slate-100 dark:border-slate-800/80 overflow-hidden">
                        
                        <!-- Top status badges -->
                        <div class="flex items-center justify-between z-10">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-white/90 dark:bg-slate-800/90 backdrop-blur-xs text-slate-700 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700">
                                {{ $template->category }}
                            </span>

                            @if($isOwned)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Unlocked
                                </span>
                            @elseif($template->is_free)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/30">
                                    Free
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/30 flex items-center gap-1">
                                    <i data-lucide="sparkles" class="w-3 h-3"></i> ${{ number_format($template->price, 2) }}
                                </span>
                            @endif
                        </div>

                        <!-- Stylized Mini Invoice Wireframe Mockup -->
                        <div class="my-auto mx-auto w-4/5 max-w-[200px] bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200/70 dark:border-slate-700 p-2.5 space-y-2 group-hover:scale-105 transition-transform duration-300">
                            <div class="flex items-center justify-between">
                                <div class="w-10 h-2 rounded bg-slate-900 dark:bg-white"></div>
                                <div class="w-6 h-1.5 rounded bg-blue-600"></div>
                            </div>
                            <div class="space-y-1">
                                <div class="w-full h-1 rounded bg-slate-200 dark:bg-slate-700"></div>
                                <div class="w-3/4 h-1 rounded bg-slate-200 dark:bg-slate-700"></div>
                            </div>
                            <div class="pt-1.5 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                                <div class="w-8 h-1 rounded bg-slate-300 dark:bg-slate-600"></div>
                                <div class="w-6 h-1.5 rounded bg-slate-800 dark:bg-slate-300"></div>
                            </div>
                        </div>

                        <!-- Hover overlay to Preview Modal -->
                        <button type="button"
                                @click.stop="openPreview('{{ $template->slug }}', '{{ addslashes($template->name) }}', '{{ $template->category }}', {{ $template->price }}, {{ $template->is_free ? 'true' : 'false' }}, {{ $isOwned ? 'true' : 'false' }})" 
                                class="absolute inset-0 bg-slate-950/70 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center p-4 text-center z-20 cursor-pointer w-full text-left">
                            <span class="w-10 h-10 rounded-full bg-white text-slate-900 flex items-center justify-center shadow-lg mb-2 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                                <i data-lucide="eye" class="w-5 h-5"></i>
                            </span>
                            <span class="text-white text-xs font-bold">Quick Modal Preview</span>
                            <span class="text-slate-300 text-[10px] mt-0.5">Watermarked interactive sample</span>
                        </button>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-base text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                    {{ $template->name }}
                                </h3>
                                @if(!$template->is_free)
                                    <span class="font-black text-sm text-slate-900 dark:text-white shrink-0">
                                        ${{ number_format($template->price, 2) }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                                {{ $template->description ?: 'Pixel-perfect typography and layout designed for modern professional invoicing.' }}
                            </p>
                        </div>

                        <!-- Action Bar -->
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-2">
                            <button type="button"
                                    @click.stop="openPreview('{{ $template->slug }}', '{{ addslashes($template->name) }}', '{{ $template->category }}', {{ $template->price }}, {{ $template->is_free ? 'true' : 'false' }}, {{ $isOwned ? 'true' : 'false' }})" 
                                    class="flex-1 px-3 py-2 rounded-xl text-center text-xs font-bold border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition flex items-center justify-center gap-1.5 cursor-pointer">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                Preview
                            </button>

                            @if($isOwned)
                                <a href="{{ route('invoices.create') }}?style={{ $template->slug }}" 
                                   class="flex-1 px-3 py-2 rounded-xl text-center text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white transition flex items-center justify-center gap-1.5 shadow-xs">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    Use Style
                                </a>
                            @elseif($template->is_free)
                                <form method="POST" action="{{ route('templates.checkout', $template->slug) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-3 py-2 rounded-xl text-center text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white transition flex items-center justify-center gap-1.5 shadow-xs">
                                        <i data-lucide="unlock" class="w-3.5 h-3.5"></i>
                                        Free Unlock
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('templates.checkout', $template->slug) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full px-3 py-2 rounded-xl text-center text-xs font-bold bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white transition flex items-center justify-center gap-1.5 shadow-xs">
                                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                        Buy ${{ number_format($template->price, 0) }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- ================= TEMPLATE PREVIEW MODAL (Zero Iframe, Instant Render) ================= -->
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
         @click.self="previewModalOpen = false">

        <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-5xl w-full max-h-[92vh] shadow-2xl flex flex-col overflow-hidden border border-slate-200 dark:border-slate-800 relative z-10">
            
            <!-- Modal Top Bar -->
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-4 bg-slate-50/90 dark:bg-slate-900/90 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-blue-600/10 text-blue-600 flex items-center justify-center shrink-0">
                        <i data-lucide="layout-template" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-black text-slate-900 dark:text-white truncate" x-text="templateName"></h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300" x-text="templateCategory"></span>
                            
                            <template x-if="templateIsOwned">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 flex items-center gap-1">
                                    <i data-lucide="check" class="w-3 h-3"></i> Unlocked
                                </span>
                            </template>
                            <template x-if="!templateIsOwned && templateIsFree">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300">
                                    Free
                                </span>
                            </template>
                            <template x-if="!templateIsOwned && !templateIsFree">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                    $<span x-text="Number(templatePrice).toFixed(2)"></span>
                                </span>
                            </template>
                        </div>
                        <p class="text-xs text-slate-400 truncate">Protected Interactive Preview • Watermarked design sample</p>
                    </div>
                </div>

                <!-- Action buttons & Close -->
                <div class="flex items-center gap-2.5 shrink-0">
                    <!-- If owned: Use in Invoice -->
                    <template x-if="templateIsOwned">
                        <a :href="'{{ route('invoices.create') }}?style=' + templateSlug" 
                           class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            <span>Use in Invoice</span>
                        </a>
                    </template>

                    <!-- If unowned & free: Free Unlock -->
                    <template x-if="!templateIsOwned && templateIsFree">
                        <form :action="'/templates/' + templateSlug + '/checkout'" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="unlock" class="w-4 h-4"></i>
                                <span>Free Unlock</span>
                            </button>
                        </form>
                    </template>

                    <!-- If unowned & premium: Buy -->
                    <template x-if="!templateIsOwned && !templateIsFree">
                        <form :action="'/templates/' + templateSlug + '/checkout'" method="POST">
                            @csrf
                            <button type="submit" class="px-5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                <span>Buy $<span x-text="Number(templatePrice).toFixed(0)"></span></span>
                            </button>
                        </form>
                    </template>

                    <!-- Close Modal -->
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
                    <template x-if="!templateIsOwned">
                        <div class="absolute inset-0 z-30 pointer-events-none overflow-hidden opacity-30 select-none">
                            <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <pattern id="wmPatternModal" width="280" height="180" patternUnits="userSpaceOnUse" patternTransform="rotate(-30)">
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
                                <rect width="100%" height="100%" fill="url(#wmPatternModal)" />
                            </svg>
                        </div>
                    </template>

                    <!-- Transparent Protective Glass Shield -->
                    <template x-if="!templateIsOwned">
                        <div class="absolute inset-0 z-20 pointer-events-auto bg-transparent cursor-default"
                             @contextmenu.prevent
                             @dragstart.prevent
                             title="Protected Preview"></div>
                    </template>

                    <!-- Instant Direct Template Inclusions -->
                    <div class="relative z-10 pointer-events-none p-2 sm:p-4">
                        @foreach($templates as $tmpl)
                            <div x-show="templateSlug === '{{ $tmpl->slug }}'" x-cloak>
                                @include('invoices.templates.' . $tmpl->slug, ['invoice' => $mockInvoice, 'isPdf' => false])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection