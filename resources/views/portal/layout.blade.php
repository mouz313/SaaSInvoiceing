<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Client Portal') - {{ $portalClient->user->company_name ?: $portalClient->user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased">
    <!-- Portal Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand / Business Info -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-lg shadow-sm">
                        {{ strtoupper(substr($portalClient->user->company_name ?: $portalClient->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 leading-tight">
                            {{ $portalClient->user->company_name ?: $portalClient->user->name }}
                        </div>
                        <span class="inline-flex items-center text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">
                            Client Portal
                        </span>
                    </div>
                </div>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('portal.dashboard') }}" 
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('portal.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('portal.invoices') }}" 
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('portal.invoices') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Invoices
                    </a>
                    <a href="{{ route('portal.estimates') }}" 
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('portal.estimates') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Estimates &amp; Quotes
                    </a>
                    <a href="{{ route('portal.payments') }}" 
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('portal.payments') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Payments
                    </a>
                    <a href="{{ route('portal.statement') }}" 
                       class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ request()->routeIs('portal.statement') ? 'bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        Account Statement
                    </a>
                </nav>

                <!-- Client Profile & Logout -->
                <div class="hidden md:flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-xs font-bold text-slate-900">{{ $portalClient->name }}</div>
                        <div class="text-[11px] text-slate-500">{{ $portalClient->company_name ?: $portalClient->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('portal.logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition border border-rose-200">
                            Log out
                        </button>
                    </form>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex items-center md:hidden">
                    <button @click="mobileOpen = !mobileOpen" type="button" class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div x-show="mobileOpen" class="md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-4 space-y-1">
            <div class="py-2 mb-2 border-b border-slate-100">
                <div class="text-xs font-bold text-slate-900">{{ $portalClient->name }}</div>
                <div class="text-[11px] text-slate-500">{{ $portalClient->company_name ?: $portalClient->email }}</div>
            </div>
            <a href="{{ route('portal.dashboard') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('portal.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Dashboard</a>
            <a href="{{ route('portal.invoices') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('portal.invoices') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Invoices</a>
            <a href="{{ route('portal.estimates') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('portal.estimates') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Estimates &amp; Quotes</a>
            <a href="{{ route('portal.payments') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('portal.payments') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Payments</a>
            <a href="{{ route('portal.statement') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('portal.statement') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-100' }}">Account Statement</a>
            <form method="POST" action="{{ route('portal.logout') }}" class="pt-2">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-rose-600 hover:bg-rose-50">
                    Log out
                </button>
            </form>
        </div>
    </header>

    <!-- Alerts Banner -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="mb-4 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs sm:text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400 mt-auto">
        <div class="max-w-7xl mx-auto px-4">
            &copy; {{ date('Y') }} {{ $portalClient->user->company_name ?: $portalClient->user->name }}. All rights reserved. Powered by SaaS Invoicing Portal.
        </div>
    </footer>
</body>
</html>