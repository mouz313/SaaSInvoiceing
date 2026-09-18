@extends('layouts.public')

@section('title', ($page->title ?? 'About Us') . ' — InvoiceHub')
@section('meta_description', $page->meta_description ?? 'Discover our mission, story, principles, and team at InvoiceHub.')

@section('content')
<div class="space-y-24 py-12 sm:py-16">

    <!-- Hero Section -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
            <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
            <span>Our Journey & Purpose</span>
        </div>
        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
            {{ $page->title ?? 'Empowering Modern Businesses to Get Paid Faster & Look Impeccable' }}
        </h1>
        <p class="text-base sm:text-xl text-slate-600 dark:text-slate-300 max-w-3xl mx-auto leading-relaxed">
            {{ $page->subtitle ?? 'InvoiceHub was built to liberate freelancers, agencies, and hyper-growth founders from ugly, clunky legacy accounting tools.' }}
        </p>
    </section>

    <!-- Stats Counter Bar -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-tr from-slate-900 via-indigo-950 to-blue-950 rounded-3xl p-8 sm:p-12 text-white shadow-2xl border border-slate-800 grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
            @php
                $stats = $page->content['stats'] ?? [
                    ['label' => 'Total Invoiced Volume', 'value' => '$14.8M+'],
                    ['label' => 'Global Customers', 'value' => '24,000+'],
                    ['label' => 'Payment Success Rate', 'value' => '99.4%'],
                    ['label' => 'Supported Currencies', 'value' => '32+'],
                ];
            @endphp
            @foreach($stats as $stat)
                <div class="space-y-1">
                    <p class="text-3xl sm:text-4xl lg:text-5xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-200">{{ $stat['value'] }}</p>
                    <p class="text-xs sm:text-sm text-slate-400 font-medium">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Mission & Story Split -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                    <i data-lucide="compass" class="w-4 h-4"></i>
                    <span>Our Mission</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                    Why We Reimagined the Everyday Invoice
                </h2>
                <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $page->content['mission_statement'] ?? 'We believe invoicing should be as enjoyable and polished as your craft. Our platform combines high-fashion PDF invoice typography with automated Stripe sync and effortless multi-currency accounting.' }}
                </p>
                <div class="p-6 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/60">
                    <p class="italic text-sm text-blue-950 dark:text-blue-200 font-medium">
                        "Your invoice is the final and often most remembered touchpoint of your client relationship. Making it memorable and executive-grade communicates respect for your work."
                    </p>
                    <span class="block text-xs font-bold text-blue-700 dark:text-blue-400 mt-2">— The InvoiceHub Design Team</span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Our Story</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white">
                    From Frustrated Freelancers to Enterprise Billing
                </h2>
                <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                    {{ $page->content['story'] ?? 'Founded in 2024 by engineers and designers tired of rigid legacy enterprise invoicing software, InvoiceHub began as an internal tool. Today, thousands of businesses rely on us to bill millions of dollars each month across 30+ currencies.' }}
                </p>
                <ul class="space-y-3 pt-2">
                    <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                        <span>Engineered with modern cloud architecture for sub-second PDF generation.</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                        <span>Zero bloated accounting ledgers — just clean, focused, beautiful invoices.</span>
                    </li>
                    <li class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-300">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5"></i>
                        <span>Direct frictionless checkout links powered by official Stripe APIs.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Core Values Cards Grid -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">The Principles That Guide Us</h2>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400">Everything we build is anchored in four foundational pillars designed to accelerate your growth.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $values = $page->content['values'] ?? [
                    ['title' => 'Design as a Differentiator', 'description' => 'An invoice is often the last impression your client gets. It should radiate prestige and trust.'],
                    ['title' => 'Speed & Frictionless Flow', 'description' => 'Generate, preview, customize, and dispatch invoices in under 45 seconds.'],
                    ['title' => 'Ironclad Security', 'description' => 'End-to-end encryption, strict role-based access, automated Stripe webhooks, and secure cloud storage.'],
                    ['title' => 'Customer Centricity', 'description' => 'Every single feature we release is shaped by real feedback from freelancers, studios, and agencies.'],
                ];
                $icons = ['palette', 'zap', 'shield-check', 'users'];
            @endphp
            @foreach($values as $index => $val)
                <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i data-lucide="{{ $icons[$index % count($icons)] }}" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white">{{ $val['title'] }}</h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $val['description'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Bottom Conversion Banner -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-8 sm:p-14 text-white shadow-xl text-center space-y-6">
            <h2 class="text-2xl sm:text-4xl font-black tracking-tight">Ready to elevate how your business gets paid?</h2>
            <p class="text-blue-100 max-w-xl mx-auto text-sm sm:text-base">Join over 24,000 freelancers, creative studios, and global agencies sending executive-grade invoices today.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-white text-slate-900 font-extrabold text-sm shadow-md hover:bg-blue-50 transition">
                    Get Started Free (5 Credits)
                </a>
                <a href="{{ route('pages.contact') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm transition">
                    Talk to Support
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
