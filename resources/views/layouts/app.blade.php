<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'InvoiceHub') }} — @yield('title', 'Dashboard')</title>

    @include('partials.head-assets')
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden" 
         @click="sidebarOpen = false" 
         x-cloak></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-72 shrink-0 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between transition-transform duration-250 ease-in-out lg:static lg:translate-x-0">
        
        <div class="flex-1 flex flex-col overflow-y-auto">
            <!-- Brand Logo Header -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    @if(setting('app_logo'))
                        <img src="{{ Storage::url(setting('app_logo')) }}" alt="{{ setting('app_name', 'InvoiceHub') }}" class="h-9 w-auto max-w-[140px] object-contain">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">{{ setting('app_name', 'InvoiceHub') }}</span>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-blue-600 dark:text-blue-400 block -mt-1">SaaS Billing</span>
                        </div>
                    @endif
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- User Profile Card in Sidebar (§3 Component Rule) -->
            <!-- User Profile Card in Sidebar (§3 Component Rule) -->
            <a href="{{ route('profile.edit') }}" class="block p-4 mx-4 mt-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 hover:border-blue-400 dark:hover:border-blue-500 transition group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-sm overflow-hidden group-hover:ring-2 group-hover:ring-blue-500 transition">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->company_name ?: Auth::user()->email }}</p>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition shrink-0"></i>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between text-xs">
                    <span class="text-slate-500 dark:text-slate-400">Plan Status</span>
                    @if(Auth::user()->package && Auth::user()->package->invoice_limit === -1)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">Unlimited Pro</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-bold text-[10px] bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400">
                            {{ Auth::user()->invoice_credits }} Credits Left
                        </span>
                    @endif
                </div>
            </a>

            <!-- Nav Groups -->
            <nav class="mt-5 px-4 space-y-6">
                <!-- Overview -->
                <div>
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Overview</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard
                        </a>
                    </div>
                </div>

                <!-- Invoicing Operations -->
                <div>
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Invoicing Operations</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('invoices.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('invoices.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                                Invoices
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('invoices.*') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ Auth::user()->invoices()->count() }}
                            </span>
                        </a>

                        <a href="{{ route('invoices.create') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                            Create Invoice
                        </a>

                        <a href="{{ route('estimates.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('estimates.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="file-check" class="w-4 h-4"></i>
                                Quotations &amp; Quotes
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('estimates.*') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ Auth::user()->estimates()->count() }}
                            </span>
                        </a>

                        <a href="{{ route('recurring.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('recurring.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                Recurring Invoices
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('recurring.*') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ Auth::user()->recurringInvoices()->count() }}
                            </span>
                        </a>

                        <a href="{{ route('clients.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('clients.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="users" class="w-4 h-4"></i>
                                Clients
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('clients.*') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ Auth::user()->clients()->count() }}
                            </span>
                        </a>

                        <a href="{{ route('products.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('products.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="package" class="w-4 h-4"></i>
                                Products & Items
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('products.*') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ Auth::user()->products()->count() }}
                            </span>
                        </a>

                        <a href="{{ route('time.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('time.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                Time Tracking
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('time.*') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ Auth::user()->timeEntries()->unbilled()->count() }}
                            </span>
                        </a>

                        <a href="{{ route('expenses.index') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('expenses.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="receipt" class="w-4 h-4"></i>
                                Expenses
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('expenses.*') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ Auth::user()->expenses()->unbilled()->count() }}
                            </span>
                        </a>
                    </div>
                </div>

                <!-- Account & Settings -->
                <div>
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Account</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                            Settings & Profile
                        </a>
                    </div>
                </div>

                <!-- Packages & Billing -->
                <div>
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Billing & Upgrade</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('home') }}#pricing" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40">
                            <i data-lucide="sparkles" class="w-4 h-4"></i>
                            Upgrade Packages
                        </a>
                    </div>
                </div>

                @if(Auth::user()->isAdmin())
                <!-- Administrator Switch -->
                <div>
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Platform Admin</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-950/40">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            Admin Control Center
                        </a>
                    </div>
                </div>
                @endif
            </nav>
        </div>

        <!-- Sidebar Footer Actions -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 space-y-2">
            <div class="flex items-center justify-between px-3 py-2">
                <span class="text-xs font-medium text-slate-500">Theme</span>
                <button type="button" class="theme-toggle-btn p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <span class="dark:hidden"><i data-lucide="moon" class="w-4 h-4"></i></span>
                    <span class="hidden dark:inline"><i data-lucide="sun" class="w-4 h-4 text-amber-400"></i></span>
                </button>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 z-30 sticky top-0">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">@yield('title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-3">
                <!-- Theme Toggle Button in Header -->
                <button type="button" class="theme-toggle-btn p-2 rounded-lg text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    <span class="dark:hidden"><i data-lucide="moon" class="w-4 h-4"></i></span>
                    <span class="hidden dark:inline"><i data-lucide="sun" class="w-4 h-4 text-amber-400"></i></span>
                </button>

                <!-- Quick Create Invoice CTA -->
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-sm shadow-blue-600/20 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">New Invoice</span>
                </a>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto px-4 sm:px-6 py-6 space-y-4">
            <!-- Page Notifications Flash -->
            @if(session('success'))
                <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center justify-between text-sm shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 flex items-center justify-between text-sm shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 dark:text-rose-400 shrink-0"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 flex items-center justify-between text-sm shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0"></i>
                        <span>{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
