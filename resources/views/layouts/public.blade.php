<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'InvoiceHub — Modern SaaS Invoicing with 4 Designer Styles')</title>
    <meta name="description" content="@yield('meta_description', 'High-fashion, modern SaaS invoicing platform with 4 designer templates, Stripe checkout, multi-currency support, and instant PDF generation.')">

    @include('partials.head-assets')
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased selection:bg-blue-600 selection:text-white transition-colors duration-200 min-h-screen flex flex-col font-['Plus_Jakarta_Sans',sans-serif]" x-data="{ mobileNav: false, fullMenuOpen: false }" @keydown.escape.window="fullMenuOpen = false">

    <!-- Top Announcement Bar (Editorial Minimalist) -->
    <div class="bg-slate-900 dark:bg-black text-slate-300 border-b border-white/10 text-[11px] font-medium py-2 px-4 tracking-wide text-center">
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2">
            <span class="bg-blue-500/20 text-blue-400 border border-blue-500/30 px-2 py-0.5 rounded-full text-[9px] uppercase font-bold tracking-widest">Avant-Garde</span>
            <span>4 Designer Invoicing Styles with Live Real-Time Preview & Instant PDF Export.</span>
            <a href="{{ route('pages.about') }}" class="underline hover:text-white transition ml-1">Explore aesthetics &rarr;</a>
        </div>
    </div>

    <!-- Navigation Bar (Editorial Minimalist) -->
    <header class="sticky top-0 z-50 bg-white/90 dark:bg-slate-950/90 backdrop-blur-xl border-b border-slate-200/70 dark:border-white/5 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                @if(setting('app_logo'))
                    <img src="{{ Storage::url(setting('app_logo')) }}" alt="{{ setting('app_name', 'InvoiceHub') }}" class="h-10 w-auto max-w-[170px] object-contain">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/25 group-hover:scale-105 transition-transform duration-300">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-xl tracking-tight text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ setting('app_name', 'InvoiceHub') }}</span>
                        <span class="text-[9px] uppercase font-bold tracking-widest text-blue-600 dark:text-blue-400 block -mt-1">Billing Suite</span>
                    </div>
                @endif
            </a>

            <!-- Header Right: Theme Toggle & Borderless MENU Trigger (in_cognita pure style) -->
            <div class="flex items-center gap-6 sm:gap-8">
                <!-- Theme Toggle -->
                <button type="button" class="theme-toggle-btn p-2 text-slate-400 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition duration-200" aria-label="Toggle Theme">
                    <span class="dark:hidden"><i data-lucide="moon" class="w-5 h-5"></i></span>
                    <span class="hidden dark:inline"><i data-lucide="sun" class="w-5 h-5 text-amber-400"></i></span>
                </button>

                <!-- Signature Borderless MENU Trigger (No oval border, in_cognita style) -->
                <button 
                    type="button" 
                    @click="fullMenuOpen = true" 
                    class="group flex items-center gap-3 text-xs uppercase font-semibold tracking-[0.25em] text-slate-900 dark:text-white hover:opacity-75 transition-all duration-300 focus:outline-none cursor-pointer" 
                    aria-label="Open Fullscreen Menu"
                >
                    <span class="tracking-[0.25em] font-bold text-xs select-none">MENU</span>
                    <span class="w-7 h-7 rounded-full border border-slate-400/90 dark:border-slate-500/90 group-hover:border-slate-900 dark:group-hover:border-white group-hover:scale-105 transition-all duration-300 flex flex-col items-center justify-center gap-[3px]">
                        <span class="w-1 h-1 rounded-full bg-slate-900 dark:bg-white transition-transform group-hover:scale-110"></span>
                        <span class="w-1 h-1 rounded-full bg-slate-900 dark:bg-white transition-transform group-hover:scale-110"></span>
                        <span class="w-1 h-1 rounded-full bg-slate-900 dark:bg-white transition-transform group-hover:scale-110"></span>
                    </span>
                </button>
            </div>
        </div>
    </header>

    <!-- Full-Screen Avant-Garde Kinetic Stretch Menu Overlay (outside header) -->
    <div 
        x-show="fullMenuOpen" 
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[9999] kinetic-menu-overlay text-white flex flex-col justify-between p-6 sm:p-12 lg:p-16 select-none overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <!-- Overlay Top Bar -->
        <div class="flex items-center justify-between w-full max-w-7xl mx-auto">
            <!-- Brand Mark -->
            <a href="{{ route('home') }}" @click="fullMenuOpen = false" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/30 group-hover:scale-105 transition duration-300">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="font-extrabold text-xl tracking-tight text-white group-hover:text-blue-400 transition">{{ setting('app_name', 'InvoiceHub') }}</span>
                    <span class="text-[9px] uppercase font-bold tracking-widest text-blue-400 block -mt-1">Billing Suite</span>
                </div>
            </a>

            <!-- Close Button (· ✕ ·) -->
            <button 
                type="button" 
                @click="fullMenuOpen = false"
                class="group flex items-center gap-2 px-4 py-2 rounded-full border border-white/20 hover:border-white/80 hover:bg-white/10 transition duration-300 text-xs font-mono tracking-widest text-slate-300 hover:text-white cursor-pointer"
                aria-label="Close menu"
            >
                <span class="text-[10px] text-slate-500 group-hover:text-white transition">&middot;</span>
                <span class="text-sm font-light leading-none">&times;</span>
                <span class="text-[10px] text-slate-500 group-hover:text-white transition">&middot;</span>
            </button>
        </div>

        <!-- Overlay Menu Body (Kinetic Stretch Items + Auth Panel) -->
        <div class="w-full max-w-2xl mx-auto my-auto py-4 sm:py-6">
            <nav class="kinetic-menu-list flex flex-col space-y-2 sm:space-y-2.5 md:space-y-3">
                <!-- 01 OVERVIEW -->
                <div>
                    <a href="{{ route('home') }}" @click="fullMenuOpen = false" class="kinetic-menu-item group text-white">
                        <span class="text-[10px] sm:text-xs font-mono text-slate-500 group-hover:text-blue-400 uppercase tracking-widest mr-3 sm:mr-5 transition duration-300">01</span>
                        <span class="word-part part-left text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>Ov</span><span class="kinetic-glitch-char" data-char="e">e</span><span>r</span>
                        </span>
                        <span class="stretch-dash"></span>
                        <span class="word-part part-right text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>v</span><span class="kinetic-glitch-char" data-char="i">i</span><span>ew</span>
                        </span>
                    </a>
                </div>

                <!-- 02 FEATURES -->
                <div>
                    <a href="{{ route('home') }}#features" @click="fullMenuOpen = false" class="kinetic-menu-item group text-white">
                        <span class="text-[10px] sm:text-xs font-mono text-slate-500 group-hover:text-blue-400 uppercase tracking-widest mr-3 sm:mr-5 transition duration-300">02</span>
                        <span class="word-part part-left text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>Fe</span><span class="kinetic-glitch-char" data-char="a">a</span><span>t</span>
                        </span>
                        <span class="stretch-dash"></span>
                        <span class="word-part part-right text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>u</span><span class="kinetic-glitch-char" data-char="r">r</span><span>es</span>
                        </span>
                    </a>
                </div>

                <!-- 03 TEMPLATES -->
                <div>
                    <a href="{{ route('home') }}#templates" @click="fullMenuOpen = false" class="kinetic-menu-item group text-white">
                        <span class="text-[10px] sm:text-xs font-mono text-slate-500 group-hover:text-blue-400 uppercase tracking-widest mr-3 sm:mr-5 transition duration-300">03</span>
                        <span class="word-part part-left text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>Tem</span><span class="kinetic-glitch-char" data-char="p">p</span>
                        </span>
                        <span class="stretch-dash"></span>
                        <span class="word-part part-right text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>la</span><span class="kinetic-glitch-char" data-char="t">t</span><span>es</span>
                        </span>
                    </a>
                </div>

                <!-- 04 PRICING -->
                <div>
                    <a href="{{ route('home') }}#pricing" @click="fullMenuOpen = false" class="kinetic-menu-item group text-white">
                        <span class="text-[10px] sm:text-xs font-mono text-slate-500 group-hover:text-blue-400 uppercase tracking-widest mr-3 sm:mr-5 transition duration-300">04</span>
                        <span class="word-part part-left text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>Pri</span><span class="kinetic-glitch-char" data-char="c">c</span>
                        </span>
                        <span class="stretch-dash"></span>
                        <span class="word-part part-right text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>i</span><span class="kinetic-glitch-char" data-char="n">n</span><span>g</span>
                        </span>
                    </a>
                </div>

                <!-- 05 ABOUT -->
                <div>
                    <a href="{{ route('pages.about') }}" @click="fullMenuOpen = false" class="kinetic-menu-item group text-white">
                        <span class="text-[10px] sm:text-xs font-mono text-slate-500 group-hover:text-blue-400 uppercase tracking-widest mr-3 sm:mr-5 transition duration-300">05</span>
                        <span class="word-part part-left text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>Ab</span>
                        </span>
                        <span class="stretch-dash"></span>
                        <span class="word-part part-right text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>o</span><span class="kinetic-glitch-char" data-char="u">u</span><span>t</span>
                        </span>
                    </a>
                </div>

                <!-- 06 FAQ -->
                <div>
                    <a href="{{ route('pages.faq') }}" @click="fullMenuOpen = false" class="kinetic-menu-item group text-white">
                        <span class="text-[10px] sm:text-xs font-mono text-slate-500 group-hover:text-blue-400 uppercase tracking-widest mr-3 sm:mr-5 transition duration-300">06</span>
                        <span class="word-part part-left text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>F</span>
                        </span>
                        <span class="stretch-dash"></span>
                        <span class="word-part part-right text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>A</span><span class="kinetic-glitch-char" data-char="Q">Q</span>
                        </span>
                    </a>
                </div>

                <!-- 07 CONTACT -->
                <div>
                    <a href="{{ route('pages.contact') }}" @click="fullMenuOpen = false" class="kinetic-menu-item group text-white">
                        <span class="text-[10px] sm:text-xs font-mono text-slate-500 group-hover:text-blue-400 uppercase tracking-widest mr-3 sm:mr-5 transition duration-300">07</span>
                        <span class="word-part part-left text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>Con</span>
                        </span>
                        <span class="stretch-dash"></span>
                        <span class="word-part part-right text-2xl sm:text-3xl md:text-[32px] font-bold tracking-tight">
                            <span>ta</span><span class="kinetic-glitch-char" data-char="c">c</span><span>t</span>
                        </span>
                    </a>
                </div>
            </nav>

            <!-- Sign In & Get Started Free Moved Directly Inside Menu -->
            <div class="pt-6 sm:pt-7 mt-5 border-t border-white/10">
                @auth
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xs shadow-md">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-slate-400">{{ auth()->user()->email }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" @click="fullMenuOpen = false" class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-500/40 hover:bg-purple-500/30 transition">
                                    Admin CMS
                                </a>
                            @endif
                            <a href="{{ route('dashboard') }}" @click="fullMenuOpen = false" class="px-4 py-2 rounded-full bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-blue-600/30 flex items-center gap-1.5">
                                <span>Dashboard</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-slate-400 hover:text-white transition px-2.5 py-1.5 font-medium cursor-pointer">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-0.5">
                            <div class="text-xs font-bold text-white tracking-wide">Ready to elevate your invoicing?</div>
                            <div class="text-[11px] text-slate-400">Create, preview, and dispatch designer invoices in under 45 seconds.</div>
                        </div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <a href="{{ route('login') }}" @click="fullMenuOpen = false" class="text-xs uppercase font-bold tracking-widest text-slate-300 hover:text-white transition py-2 px-3.5 border border-white/20 hover:border-white rounded-full">
                                Sign In &rarr;
                            </a>
                            <a href="{{ route('register') }}" @click="fullMenuOpen = false" class="px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold uppercase tracking-widest transition shadow-lg shadow-blue-600/30 hover:scale-105 transform flex items-center gap-2">
                                <span>Get Started Free</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Overlay Bottom Bar (Footer Pills & Links) -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6 w-full max-w-7xl mx-auto pt-6 border-t border-white/10 text-xs text-slate-400">
            <!-- Left: Country/Language Pill + Social Links -->
            <div class="flex items-center gap-5 sm:gap-8 flex-wrap justify-center sm:justify-start">
                <span class="px-2.5 py-1 rounded-full border border-white/20 text-[11px] font-mono tracking-wider text-slate-300">
                    IN
                </span>
                <a href="{{ setting('social_linkedin', 'https://linkedin.com') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white transition flex items-center gap-1 font-medium">
                    LinkedIn <span class="text-[10px] text-slate-500">&nearr;</span>
                </a>
                <a href="{{ setting('social_twitter', 'https://twitter.com') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white transition flex items-center gap-1 font-medium">
                    Twitter <span class="text-[10px] text-slate-500">&nearr;</span>
                </a>
                <a href="{{ setting('social_github', 'https://github.com') }}" target="_blank" rel="noopener noreferrer" class="hover:text-white transition flex items-center gap-1 font-medium">
                    GitHub <span class="text-[10px] text-slate-500">&nearr;</span>
                </a>
            </div>

            <!-- Right: Contact Us / Action Pill Button -->
            <div class="flex items-center gap-3">
                <a href="{{ route('pages.contact') }}" @click="fullMenuOpen = false" class="group px-6 py-2.5 rounded-full border border-white/30 hover:border-white text-xs font-semibold uppercase tracking-wider text-white hover:bg-white/10 transition flex items-center gap-2.5">
                    <span>Contact us</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 group-hover:scale-125 transition"></span>
                </a>
            </div>
        </div>
    </div>



    <!-- Global Alerts -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center gap-3 text-sm shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 flex items-center gap-3 text-sm shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 shrink-0"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Body Slot -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Modern Global Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
                <!-- Col 1: Brand & Bio (2 cols) -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        @if(setting('app_logo'))
                            <img src="{{ Storage::url(setting('app_logo')) }}" alt="{{ setting('app_name', 'InvoiceHub') }}" class="h-9 w-auto max-w-[160px] object-contain">
                        @else
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-600/20">
                                <i data-lucide="receipt" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <span class="font-extrabold text-xl tracking-tight text-slate-900 dark:text-white">{{ setting('app_name', 'InvoiceHub') }}</span>
                                <span class="text-[9px] uppercase font-bold tracking-widest text-blue-600 dark:text-blue-400 block -mt-1">Billing Suite</span>
                            </div>
                        @endif
                    </a>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed max-w-sm">
                        The modern standard for client invoicing. Generate executive-grade invoices in 4 designer aesthetics with instant PDF export, Stripe checkouts, and automatic bookkeeping.
                    </p>
                    
                    <!-- Social Media Links (Crisp Inline SVGs) -->
                    <div class="flex items-center gap-2 pt-2">
                        @if(setting('social_twitter'))
                            <a href="{{ setting('social_twitter') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white text-slate-600 dark:text-slate-300 flex items-center justify-center transition shadow-xs" aria-label="Twitter / X" title="Twitter / X">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                        @endif
                        @if(setting('social_linkedin'))
                            <a href="{{ setting('social_linkedin') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-blue-600 dark:hover:text-blue-400 text-slate-600 dark:text-slate-300 flex items-center justify-center transition shadow-xs" aria-label="LinkedIn" title="LinkedIn">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 8.76c-.97 0-1.75-.79-1.75-1.76s.78-1.75 1.75-1.75 1.75.78 1.75 1.75-.78 1.76-1.75 1.76m1.39 9.74v-8.37H5.07v8.37h2.78z"/>
                                </svg>
                            </a>
                        @endif
                        @if(setting('social_github'))
                            <a href="{{ setting('social_github') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-white text-slate-600 dark:text-slate-300 flex items-center justify-center transition shadow-xs" aria-label="GitHub" title="GitHub">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
                                </svg>
                            </a>
                        @endif
                        @if(setting('social_facebook'))
                            <a href="{{ setting('social_facebook') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/60 hover:text-blue-600 dark:hover:text-blue-400 text-slate-600 dark:text-slate-300 flex items-center justify-center transition shadow-xs" aria-label="Facebook" title="Facebook">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/>
                                </svg>
                            </a>
                        @endif
                        @if(setting('social_instagram'))
                            <a href="{{ setting('social_instagram') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/60 hover:text-rose-600 dark:hover:text-rose-400 text-slate-600 dark:text-slate-300 flex items-center justify-center transition shadow-xs" aria-label="Instagram" title="Instagram">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/>
                                </svg>
                            </a>
                        @endif
                        @if(setting('social_youtube'))
                            <a href="{{ setting('social_youtube') }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/60 hover:text-rose-600 dark:hover:text-rose-400 text-slate-600 dark:text-slate-300 flex items-center justify-center transition shadow-xs" aria-label="YouTube" title="YouTube">
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Col 2: Product -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Product</h3>
                    <ul class="space-y-2.5 text-sm text-slate-500 dark:text-slate-400">
                        <li><a href="{{ route('home') }}#features" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Key Features</a></li>
                        <li><a href="{{ route('home') }}#templates" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Designer Styles</a></li>
                        <li><a href="{{ route('home') }}#pricing" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Pricing & Plans</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Create Account</a></li>
                    </ul>
                </div>

                <!-- Col 3: Company & Resources -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Company</h3>
                    <ul class="space-y-2.5 text-sm text-slate-500 dark:text-slate-400">
                        <li><a href="{{ route('pages.about') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">About Us</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">FAQ & Help</a></li>
                        <li><a href="{{ route('pages.contact') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Contact Us</a></li>
                        <li><a href="mailto:{{ setting('support_email', 'support@invoicehub.test') }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">Support Email</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & HQ -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white mb-4">Global HQ</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 leading-relaxed mb-2">
                        {{ setting('office_address', '100 Market St, Suite 400, San Francisco, CA 94105') }}
                    </p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Phone:</span> {{ setting('support_phone', '+1 (800) 555-0199') }}
                    </p>
                </div>
            </div>

            <!-- Bottom Row: Copyright & Disclaimers -->
            <div class="mt-12 pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
                <p>{{ setting('copyright_text', '© 2026 InvoiceHub Inc. All rights reserved.') }}</p>
                <div class="flex items-center gap-6">
                    <a href="{{ route('pages.about') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Privacy</a>
                    <a href="{{ route('pages.about') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Terms of Service</a>
                    <a href="{{ route('pages.contact') }}" class="hover:text-slate-600 dark:hover:text-slate-300 transition">Security</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
