<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin Portal — @yield('title', 'Overview')</title>

    @include('partials.head-assets')
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased flex transition-colors duration-200" x-data="{ sidebarOpen: false }">

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

    <!-- Admin Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-50 w-72 shrink-0 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between transition-transform duration-250 ease-in-out lg:static lg:translate-x-0">
        
        <div class="flex-1 flex flex-col overflow-y-auto">
            <!-- Brand Logo Header -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    @if(setting('app_logo'))
                        <img src="{{ Storage::url(setting('app_logo')) }}" alt="{{ setting('app_name', 'InvoiceHub') }}" class="h-9 w-auto max-w-[140px] object-contain">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-slate-900 via-indigo-950 to-blue-600 dark:from-blue-600 dark:to-indigo-500 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                            <i data-lucide="shield-check" class="w-5 h-5 text-blue-300 dark:text-white"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-lg tracking-tight text-slate-900 dark:text-white">{{ setting('app_name', 'InvoiceHub') }}</span>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-blue-600 dark:text-blue-400 block -mt-1">Admin Command</span>
                        </div>
                    @endif
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Admin Profile Card -->
            <a href="{{ route('profile.edit') }}" class="block p-4 mx-4 mt-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60 hover:border-blue-400 dark:hover:border-blue-500 transition group">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-xs group-hover:scale-105 transition">
                        AD
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">{{ Auth::user()->name }}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Super Admin</span>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition shrink-0"></i>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="mt-6 px-4 space-y-6">
                <div>
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Platform Management</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            Dashboard Metrics
                        </a>

                        <a href="{{ route('admin.users') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.users') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="users" class="w-4 h-4"></i>
                                Users & Credits
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('admin.users') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ \App\Models\User::count() }}
                            </span>
                        </a>

                        <a href="{{ route('admin.invoices') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.invoices') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="receipt" class="w-4 h-4"></i>
                                Global Invoices
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ request()->routeIs('admin.invoices') ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                {{ \App\Models\Invoice::count() }}
                            </span>
                        </a>

                        <a href="{{ route('admin.packages') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.packages') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                            Pricing Packages
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">CMS & Content</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('admin.cms.pages') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.cms.pages*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                            Page Contents
                        </a>

                        <a href="{{ route('admin.cms.faqs') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.cms.faqs*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <i data-lucide="help-circle" class="w-4 h-4"></i>
                            FAQ Manager
                        </a>

                        <a href="{{ route('admin.cms.inquiries') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.cms.inquiries*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <div class="flex items-center gap-3">
                                <i data-lucide="inbox" class="w-4 h-4"></i>
                                Inquiries & Leads
                            </div>
                            @php $unreadInquiries = \App\Models\ContactInquiry::unread()->count(); @endphp
                            @if($unreadInquiries > 0)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-rose-500 text-white font-bold">
                                    {{ $unreadInquiries }}
                                </span>
                            @endif
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">System & Navigation</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('admin.settings.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.settings.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <i data-lucide="sliders" class="w-4 h-4"></i>
                            System Settings
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('profile.*') ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            <i data-lucide="user-check" class="w-4 h-4"></i>
                            Admin Profile
                        </a>

                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <i data-lucide="arrow-left-circle" class="w-4 h-4 text-blue-600"></i>
                            Switch to User Portal
                        </a>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 space-y-2">
            <!-- Theme Toggle Bar -->
            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/60">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Interface Theme</span>
                <button type="button" class="theme-toggle-btn p-1.5 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <span class="dark:hidden"><i data-lucide="moon" class="w-4 h-4"></i></span>
                    <span class="hidden dark:inline"><i data-lucide="sun" class="w-4 h-4 text-amber-400"></i></span>
                </button>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- Topbar -->
        <header class="h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-4 sm:px-6 z-30 sticky top-0">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                
                <!-- Breadcrumbs (§3 Component Rule) -->
                <div class="flex items-center gap-2 text-xs sm:text-sm">
                    <span class="font-semibold text-slate-400">Admin</span>
                    <span class="text-slate-300 dark:text-slate-600">/</span>
                    <span class="font-bold text-slate-900 dark:text-white">@yield('title', 'Dashboard')</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Theme Toggle Button -->
                <button type="button" class="theme-toggle-btn p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Toggle theme">
                    <span class="dark:hidden"><i data-lucide="moon" class="w-4 h-4"></i></span>
                    <span class="hidden dark:inline"><i data-lucide="sun" class="w-4 h-4 text-amber-400"></i></span>
                </button>

                <!-- Status Pill -->
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Stripe & DB Connected
                </span>

                <!-- Return to User View CTA -->
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-750 text-xs font-bold text-slate-700 dark:text-slate-200 transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-blue-600"></i>
                    User App
                </a>
            </div>
        </header>

        <!-- Flash messages -->
        <div class="px-4 sm:px-6 pt-4">
            @if(session('success'))
                <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm mb-4 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm mb-4 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
        </div>

        <!-- Main Body -->
        <main class="flex-1 overflow-y-auto px-4 sm:px-6 py-4">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
