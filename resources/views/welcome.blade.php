@extends('layouts.public')

@section('title', 'InvoiceHub — Modern SaaS Invoicing with 4 Designer Styles')
@section('meta_description', 'Generate executive-grade invoices in 4 designer aesthetics with instant PDF export, Stripe checkouts, and automatic bookkeeping.')

@section('content')
<div class="space-y-24 sm:space-y-32 pb-24 overflow-hidden" x-data="{
    activeStyle: 'minimalist',
    billingCycle: 'monthly',
    openFaq: null,
    toggleFaq(id) {
        this.openFaq = this.openFaq === id ? null : id;
    }
}">

    <!-- 1. Modern Hero Section with Aura Glow -->
    <section class="relative pt-12 sm:pt-20 lg:pt-24">
        <!-- Radial Background Aura -->
        <div class="absolute inset-0 -z-10 flex items-center justify-center">
            <div class="w-[600px] sm:w-[900px] h-[400px] bg-gradient-to-tr from-blue-500/20 via-indigo-500/20 to-purple-500/20 blur-[130px] rounded-full pointer-events-none"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-extrabold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-sm animate-pulse">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>Next-Gen Invoicing Engine • 4 Designer Layouts</span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-slate-900 dark:text-white tracking-tight leading-[1.08] max-w-4xl mx-auto">
                Invoices So Elegant, <br class="hidden sm:inline">
                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600">Clients Pay On Sight.</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-base sm:text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed">
                Step beyond hideous spreadsheets and boring accounting PDFs. Create, preview, and dispatch magazine-grade invoices in under 45 seconds with instant Stripe payment links.
            </p>

            <!-- CTA Buttons & Demo Logins -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                @auth
                    <a href="{{ route('invoices.create') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-black text-base shadow-xl shadow-blue-600/30 hover:scale-[1.02] transition flex items-center justify-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        <span>Create New Invoice</span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 font-bold text-base hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        Open Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-black text-base shadow-xl shadow-blue-600/30 hover:scale-[1.02] transition flex items-center justify-center gap-2">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                        <span>Start Free (5 Credits)</span>
                    </a>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <a href="{{ route('auth.demo-login', 'user') }}" class="flex-1 sm:flex-initial px-5 py-4 rounded-2xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-sm hover:opacity-90 transition">
                            Demo User
                        </a>
                        <a href="{{ route('auth.demo-login', 'admin') }}" class="flex-1 sm:flex-initial px-5 py-4 rounded-2xl bg-purple-100 text-purple-900 dark:bg-purple-950/80 dark:text-purple-200 border border-purple-300 dark:border-purple-800 font-bold text-sm hover:bg-purple-200 transition">
                            Demo Admin
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Trust Markers -->
            <div class="pt-6 flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i>
                    <span>No Credit Card Required</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i>
                    <span>Stripe & Google OAuth Included</span>
                </div>
                <div class="flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-500"></i>
                    <span>Instant Vector PDF Export</span>
                </div>
            </div>

            <!-- Hero Interactive Preview Showcase -->
            <div class="pt-8 max-w-5xl mx-auto">
                <div class="rounded-3xl p-2 sm:p-4 bg-gradient-to-b from-slate-200/80 to-slate-100 dark:from-slate-800/80 dark:to-slate-900/60 border border-slate-200 dark:border-slate-800 shadow-2xl">
                    <div class="rounded-2xl overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-8 md:p-10 text-left space-y-5 sm:space-y-6">
                        
                        <!-- Top Toolbar of the Preview Card -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4 sm:pb-5">
                            <div>
                                <div class="flex items-center gap-2 text-[11px] sm:text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    <span>Interactive Preview • Select Style Below</span>
                                </div>
                                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">Acme Design Studio &rarr; Globex Corporation</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-[11px] sm:text-xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                                    STATUS: PAID ($4,850.00)
                                </span>
                            </div>
                        </div>

                        <!-- 4 Designer Style Tabs in Preview -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button @click="activeStyle = 'minimalist'" :class="activeStyle === 'minimalist' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                                <i data-lucide="layout" class="w-3.5 h-3.5"></i>
                                <span>Minimalist</span>
                            </button>
                            <button @click="activeStyle = 'corporate'" :class="activeStyle === 'corporate' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                                <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                                <span>Corporate</span>
                            </button>
                            <button @click="activeStyle = 'modern_tech'" :class="activeStyle === 'modern_tech' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                                <i data-lucide="cpu" class="w-3.5 h-3.5"></i>
                                <span>Modern Tech</span>
                            </button>
                            <button @click="activeStyle = 'elegant_serif'" :class="activeStyle === 'elegant_serif' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200'" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2">
                                <i data-lucide="feather" class="w-3.5 h-3.5"></i>
                                <span>Elegant Serif</span>
                            </button>
                        </div>

                        <!-- Live Rendered Sample Canvas (Mobile-Optimized Responsive Hierarchy) -->
                        <div class="p-4 sm:p-6 rounded-2xl border transition-all duration-300"
                             :class="{
                                'bg-white text-slate-900 border-slate-200 shadow-sm': activeStyle === 'minimalist',
                                'bg-slate-900 text-white border-blue-900 shadow-lg': activeStyle === 'corporate',
                                'bg-zinc-950 text-cyan-400 border-cyan-800 font-mono shadow-xl': activeStyle === 'modern_tech',
                                'bg-amber-50/50 text-stone-900 border-amber-200 font-serif shadow-sm': activeStyle === 'elegant_serif'
                             }">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b"
                                 :class="{
                                    'border-slate-100': activeStyle === 'minimalist',
                                    'border-slate-800': activeStyle === 'corporate',
                                    'border-cyan-900': activeStyle === 'modern_tech',
                                    'border-amber-200': activeStyle === 'elegant_serif'
                                 }">
                                <div>
                                    <p class="text-[10px] sm:text-xs font-bold uppercase tracking-widest opacity-60">INVOICE #INV-2026-0042</p>
                                    <h4 class="text-base sm:text-xl font-black mt-0.5 leading-tight">Brand Identity & Design System</h4>
                                </div>
                                <div class="flex sm:flex-col items-baseline sm:items-end justify-between sm:justify-center border-t sm:border-t-0 pt-2 sm:pt-0 border-current/10">
                                    <p class="text-[10px] sm:text-xs opacity-60 uppercase font-semibold">Amount Due</p>
                                    <p class="text-xl sm:text-2xl font-black tracking-tight">$4,850.00 <span class="text-xs font-normal opacity-70">USD</span></p>
                                </div>
                            </div>
                            <div class="pt-3 text-[11px] sm:text-xs opacity-75 flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                                <span><strong class="opacity-90">Billed to:</strong> Globex Corp (finance@globex.test)</span>
                                <span><strong class="opacity-90">Due date:</strong> Net 15 Days</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- 2. Metrics & Live Stats Bar -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 rounded-3xl p-8 sm:p-12 text-white shadow-xl border border-slate-800 grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
            <div class="space-y-1">
                <p class="text-3xl sm:text-4xl lg:text-5xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-200">$14.8M+</p>
                <p class="text-xs sm:text-sm text-slate-400 font-medium">Billed by Users</p>
            </div>
            <div class="space-y-1">
                <p class="text-3xl sm:text-4xl lg:text-5xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-200">24,000+</p>
                <p class="text-xs sm:text-sm text-slate-400 font-medium">Active Accounts</p>
            </div>
            <div class="space-y-1">
                <p class="text-3xl sm:text-4xl lg:text-5xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-200">99.4%</p>
                <p class="text-xs sm:text-sm text-slate-400 font-medium">Payment Success</p>
            </div>
            <div class="space-y-1">
                <p class="text-3xl sm:text-4xl lg:text-5xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-indigo-200">4.9 / 5</p>
                <p class="text-xs sm:text-sm text-slate-400 font-medium">Customer Rating</p>
            </div>
        </div>
    </section>

    <!-- 3. Core Features Grid -->
    <section id="features" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Everything You Need</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Engineered for Frictionless Growth</h2>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400">Stop wasting billable hours wrestling with clumsy accounting portals. InvoiceHub provides everything modern teams need.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Feature 1 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="palette" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">4 Designer Styles</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Choose between Minimalist Clean, Corporate Dark, Cyberpunk Modern Tech, or Elegant Luxury Serif. Switch styles on any invoice with 1 click.
                </p>
            </div>

            <!-- Feature 2 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Stripe Checkout & Webhooks</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Upgrade plans seamlessly with Stripe Checkout. Webhooks sync transactions automatically, allocating credits and refreshing plan limits immediately.
                </p>
            </div>

            <!-- Feature 3 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <i data-lucide="file-text" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Instant Vector PDF Engine</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Download crisp, printable PDF documents tailored for international A4 and US Letter sizes with high-resolution typography and brand accents.
                </p>
            </div>

            <!-- Feature 4 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i data-lucide="globe-2" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Multi-Currency Ready</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Support for USD, EUR, GBP, CAD, AUD, PKR, INR, AED, SAR, and JPY with accurate symbols and international bank remittance presets.
                </p>
            </div>

            <!-- Feature 5 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Integrated Client CRM</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    Store recurring client details, tax registration IDs, billing addresses, and payment history in one place to autofill invoices in seconds.
                </p>
            </div>

            <!-- Feature 6 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Firebase & Google OAuth</h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                    One-tap Google sign-in powered by official Firebase SDK, alongside Argon2 password encryption, CSRF protection, and role middleware.
                </p>
            </div>
        </div>
    </section>

    <!-- 4. The 4 Designer Styles Showcase -->
    <section id="templates" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Tailored Aesthetics</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">A Style for Every Industry</h2>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400">Match your client's industry standard with precision-engineered typographic hierarchy.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Style 1: Minimalist -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="h-32 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Minimalist</span>
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Clean & Crisp</p>
                        <p class="text-[10px] text-slate-400">Generous whitespace, Swiss grid</p>
                    </div>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Minimalist Clean</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Perfect for creative directors, UI/UX designers, copywriters, and consultants.</p>
            </div>

            <!-- Style 2: Corporate -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="h-32 rounded-2xl bg-slate-900 text-white border border-slate-800 p-4 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Corporate</span>
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-white">Executive Dark</p>
                        <p class="text-[10px] text-slate-400">Structured tables & navy header</p>
                    </div>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Corporate Enterprise</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Ideal for agencies, management consultants, and B2B SaaS corporations.</p>
            </div>

            <!-- Style 3: Modern Tech -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="h-32 rounded-2xl bg-zinc-950 text-cyan-400 border border-cyan-900/60 p-4 flex flex-col justify-between font-mono">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase text-cyan-500">Modern Tech</span>
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-cyan-300">Mono Monospaced</p>
                        <p class="text-[10px] text-cyan-600">Terminal codes & dark canvas</p>
                    </div>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Modern Tech</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Tailored for full-stack developers, DevOps engineers, and AI developers.</p>
            </div>

            <!-- Style 4: Elegant Serif -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="h-32 rounded-2xl bg-amber-50/70 dark:bg-stone-900 text-stone-900 dark:text-amber-100 border border-amber-200 dark:border-amber-900/60 p-4 flex flex-col justify-between font-serif">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-stone-500 uppercase tracking-widest">Prestige</span>
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                    </div>
                    <div>
                        <p class="text-xs font-bold italic">Playfair Elegance</p>
                        <p class="text-[10px] text-stone-400 font-sans">Warm paper tone & gold lines</p>
                    </div>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Elegant Serif</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Crafted for architecture firms, law partners, photographers, and luxury studios.</p>
            </div>
        </div>
    </section>

    <!-- 5. Testimonials & Social Proof -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Wall of Love</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Trusted by Industry Leaders</h2>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400">See what creative directors and founders say about using InvoiceHub.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Review 1 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center gap-1 text-amber-400">
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Our agency switched from QuickBooks to InvoiceHub for our client invoices. The Elegant Serif style alone made our clients comment on how prestigious our billing looked!"
                </p>
                <div class="pt-2 flex items-center gap-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-800 font-bold flex items-center justify-center text-xs">
                        SM
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Sophia Miller</p>
                        <p class="text-[11px] text-slate-400">Managing Partner, Studio Apex</p>
                    </div>
                </div>
            </div>

            <!-- Review 2 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center gap-1 text-amber-400">
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "The Modern Tech template with monospaced code fonts was made for developers. Plus, the integrated Stripe checkout link cuts our average invoice turnaround time to under 48 hours."
                </p>
                <div class="pt-2 flex items-center gap-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-800 font-bold flex items-center justify-center text-xs">
                        DK
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Devon Vance</p>
                        <p class="text-[11px] text-slate-400">Fractional CTO & Consultant</p>
                    </div>
                </div>
            </div>

            <!-- Review 3 -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center gap-1 text-amber-400">
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                    <i data-lucide="star" class="w-4 h-4 fill-amber-400"></i>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed italic">
                    "Everything from client management to multi-currency and dark mode works effortlessly. The customer support responded within 15 minutes when I had a custom domain question."
                </p>
                <div class="pt-2 flex items-center gap-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-800 font-bold flex items-center justify-center text-xs">
                        ET
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">Elena Thorne</p>
                        <p class="text-[11px] text-slate-400">Founder, Thorne Media UK</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. Pricing & Packages Comparison -->
    <section id="pricing" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <div class="text-center space-y-3 max-w-2xl mx-auto">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Simple & Transparent</span>
            <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Predictable Pricing for High Earners</h2>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400">No hidden fees, no credit card lock-ins. Upgrade or cancel any time.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            <!-- Starter Plan -->
            <div class="rounded-3xl p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-8">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">Starter Plan</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white">Freelancer</h3>
                    <p class="text-xs text-slate-500">Perfect for solo freelancers getting started with recurring billing.</p>
                    <div class="flex items-baseline gap-1 pt-2">
                        <span class="text-4xl font-black text-slate-900 dark:text-white">$9</span>
                        <span class="text-xs text-slate-400 font-medium">/ month</span>
                    </div>
                    <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> 10 Invoices / Month</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> All 4 Designer Styles</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Unlimited Clients</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Instant PDF Export</li>
                    </ul>
                </div>
                <div>
                    @auth
                        <form action="{{ route('stripe.checkout', 1) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-bold text-xs transition">
                                Choose Starter
                            </button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" class="block w-full text-center py-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-900 dark:text-white font-bold text-xs transition">
                            Get Started Free
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Pro Plan (Popular Highlight) -->
            <div class="rounded-3xl p-8 bg-gradient-to-b from-blue-900 via-slate-900 to-indigo-950 text-white border-2 border-blue-500 shadow-2xl shadow-blue-500/20 flex flex-col justify-between space-y-8 relative">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-md">
                    Most Popular
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30">Professional</span>
                    </div>
                    <h3 class="text-2xl font-black text-white">Agency & Studio</h3>
                    <p class="text-xs text-blue-200">High-volume billing for growing teams and boutique creative agencies.</p>
                    <div class="flex items-baseline gap-1 pt-2">
                        <span class="text-4xl font-black text-white">$29</span>
                        <span class="text-xs text-blue-200 font-medium">/ month</span>
                    </div>
                    <ul class="space-y-3 text-xs text-blue-100 pt-4 border-t border-blue-900/60">
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-blue-400"></i> 50 Invoices / Month</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-blue-400"></i> All 4 Designer Styles</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-blue-400"></i> Priority PDF Engine</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-blue-400"></i> Custom Remittance Presets</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-blue-400"></i> Direct Stripe Webhook Sync</li>
                    </ul>
                </div>
                <div>
                    @auth
                        <form action="{{ route('stripe.checkout', 2) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-3.5 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-black text-xs shadow-lg transition">
                                Upgrade to Pro
                            </button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" class="block w-full text-center py-3.5 rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-black text-xs shadow-lg transition">
                            Start Free Trial
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Enterprise Plan -->
            <div class="rounded-3xl p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-8">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/80 dark:text-purple-300">Unlimited</span>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white">Enterprise VIP</h3>
                    <p class="text-xs text-slate-500">Uncapped volume, dedicated advisory, and custom integration setup.</p>
                    <div class="flex items-baseline gap-1 pt-2">
                        <span class="text-4xl font-black text-slate-900 dark:text-white">$79</span>
                        <span class="text-xs text-slate-400 font-medium">/ month</span>
                    </div>
                    <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-300 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> <strong>Unlimited</strong> Invoices</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Dedicated Account Manager</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> Custom CSS & Typography Styling</li>
                        <li class="flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-emerald-500"></i> SLA 99.9% Guarantee</li>
                    </ul>
                </div>
                <div>
                    @auth
                        <form action="{{ route('stripe.checkout', 3) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs transition">
                                Get Enterprise
                            </button>
                        </form>
                    @else
                        <a href="{{ route('pages.contact') }}" class="block w-full text-center py-3 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs transition">
                            Contact Enterprise Sales
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- 7. FAQ Preview Section with Accordion -->
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="text-center space-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">Got Questions?</span>
            <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Frequently Asked Questions</h2>
        </div>

        <div class="space-y-4">
            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <button type="button" @click="toggleFaq(1)" class="w-full px-6 py-5 text-left flex items-center justify-between font-bold text-slate-900 dark:text-white text-sm sm:text-base hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <span>What makes InvoiceHub different from other invoicing tools?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="openFaq === 1 ? 'rotate-180 text-blue-600' : ''"></i>
                </button>
                <div x-show="openFaq === 1" x-collapse x-cloak class="px-6 pb-6 text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed border-t border-slate-100 dark:border-slate-800">
                    We focus on speed and executive aesthetics. With 4 unique designer styles, live interactive preview, instant PDF export, and integrated Stripe Checkout, your clients get an invoice that communicates prestige.
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <button type="button" @click="toggleFaq(2)" class="w-full px-6 py-5 text-left flex items-center justify-between font-bold text-slate-900 dark:text-white text-sm sm:text-base hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <span>How do invoice credits work?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="openFaq === 2 ? 'rotate-180 text-blue-600' : ''"></i>
                </button>
                <div x-show="openFaq === 2" x-collapse x-cloak class="px-6 pb-6 text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed border-t border-slate-100 dark:border-slate-800">
                    Every new user gets 5 starter credits. Upgrading to Starter or Pro grants monthly allocations. If you choose our Enterprise tier, you unlock completely unlimited invoice generation with zero deductions.
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <button type="button" @click="toggleFaq(3)" class="w-full px-6 py-5 text-left flex items-center justify-between font-bold text-slate-900 dark:text-white text-sm sm:text-base hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <span>Can I customize my company logo and bank details?</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform" :class="openFaq === 3 ? 'rotate-180 text-blue-600' : ''"></i>
                </button>
                <div x-show="openFaq === 3" x-collapse x-cloak class="px-6 pb-6 text-xs sm:text-sm text-slate-500 dark:text-slate-400 leading-relaxed border-t border-slate-100 dark:border-slate-800">
                    Yes! Visit your Profile & Settings page to upload your studio logo, set default currency, and save bank remittance wire instructions that autofill every new invoice.
                </div>
            </div>
        </div>

        <div class="text-center pt-2">
            <a href="{{ route('pages.faq') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 dark:text-blue-400 hover:underline">
                <span>View all frequently asked questions</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </section>

    <!-- 8. High-Impact Bottom CTA Banner -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 p-8 sm:p-16 text-white shadow-2xl text-center space-y-6 relative overflow-hidden">
            <div class="relative z-10 space-y-4">
                <h2 class="text-3xl sm:text-5xl font-black tracking-tight">Upgrade Your Client Billing Experience Today</h2>
                <p class="text-blue-100 max-w-2xl mx-auto text-sm sm:text-base">Join over 24,000 businesses sending executive-grade invoices. Setup takes under 60 seconds.</p>
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white text-slate-900 font-extrabold text-base shadow-lg hover:bg-blue-50 transition">
                        Create Free Account (5 Credits)
                    </a>
                    <a href="{{ route('pages.contact') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-base transition">
                        Contact Support Team
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
