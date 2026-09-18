@extends('layouts.public')

@section('title', ($page->title ?? 'FAQ') . ' — InvoiceHub')
@section('meta_description', $page->meta_description ?? 'Frequently asked questions about InvoiceHub plans, billing, PDF generation, and Stripe integrations.')

@section('content')
<div class="space-y-16 py-12 sm:py-16" x-data="{
    search: '',
    selectedCategory: '{{ $currentCategory }}',
    openItem: null,
    toggle(id) {
        this.openItem = this.openItem === id ? null : id;
    }
}">

    <!-- Header Section -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
            <i data-lucide="help-circle" class="w-3.5 h-3.5"></i>
            <span>Knowledge Base & Support</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
            {{ $page->title ?? 'Frequently Asked Questions' }}
        </h1>
        <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-2xl mx-auto">
            {{ $page->subtitle ?? 'Everything you need to know about InvoiceHub subscriptions, Stripe checkouts, invoice customization, and credits.' }}
        </p>

        <!-- Search Bar -->
        <div class="max-w-xl mx-auto pt-4">
            <div class="relative">
                <i data-lucide="search" class="w-5 h-5 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2"></i>
                <input 
                    type="text" 
                    x-model="search" 
                    placeholder="Search questions by keyword (e.g. Stripe, PDF, credits)..." 
                    class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
            </div>
        </div>
    </section>

    <!-- Category Filters -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-center gap-2">
            @foreach($categories as $catKey => $catLabel)
                <a 
                    href="{{ route('pages.faq', $catKey === 'all' ? [] : ['category' => $catKey]) }}"
                    class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $currentCategory === $catKey ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                >
                    {{ $catLabel }}
                </a>
            @endforeach
        </div>
    </section>

    <!-- Accordion List -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
        @forelse($faqs as $faq)
            <div 
                x-show="search === '' || '{{ strtolower(addslashes($faq->question . ' ' . $faq->answer)) }}'.includes(search.toLowerCase())"
                class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition"
            >
                <button 
                    type="button" 
                    @click="toggle({{ $faq->id }})" 
                    class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition"
                >
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                            {{ $faq->category }}
                        </span>
                        <h3 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">{{ $faq->question }}</h3>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-500 shrink-0">
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="openItem === {{ $faq->id }} ? 'rotate-180 text-blue-600' : ''"></i>
                    </div>
                </button>

                <div 
                    x-show="openItem === {{ $faq->id }}" 
                    x-collapse 
                    x-cloak
                    class="px-6 pb-6 pt-2 text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/80"
                >
                    {{ $faq->answer }}
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-slate-500">
                <i data-lucide="help-circle" class="w-12 h-12 mx-auto text-slate-400 mb-3"></i>
                <p class="font-bold">No FAQ questions found in this category.</p>
            </div>
        @endforelse
    </section>

    <!-- Still Have Questions Prompt -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="p-8 sm:p-10 rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 text-white text-center space-y-4 shadow-xl border border-slate-800">
            <h3 class="text-xl sm:text-2xl font-black">Couldn't find the answer you're looking for?</h3>
            <p class="text-slate-300 text-xs sm:text-sm max-w-lg mx-auto">Our advisory and engineering support team is available around the clock to assist you with custom integrations, billing, and templates.</p>
            <div class="pt-2">
                <a href="{{ route('pages.contact') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md transition">
                    <i data-lucide="message-square" class="w-4 h-4"></i>
                    <span>Contact Customer Support</span>
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
