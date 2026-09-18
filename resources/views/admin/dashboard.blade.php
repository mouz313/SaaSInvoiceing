@extends('layouts.admin')

@section('title', 'Platform Metrics & Overview')

@section('content')
<div class="space-y-6">

    <!-- Top Admin Banner -->
    <div class="p-6 rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-white backdrop-blur-xs mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Live Platform Control Center
                </div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight">System Performance & Oversight</h2>
                <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl">
                    Real-time monitoring of registered user accounts, active Stripe subscriptions, and system-wide billing.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs sm:text-sm shadow-md transition transform hover:-translate-y-0.5">
                    <i data-lucide="users" class="w-4 h-4"></i>
                    Manage Users
                </a>
                <a href="{{ route('admin.invoices') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs sm:text-sm backdrop-blur-xs transition">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    Audit Invoices
                </a>
            </div>
        </div>
        <!-- Decorative shape -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-blue-500/10 pointer-events-none"></div>
    </div>

    <!-- 4 KPI Stat Cards (§3 Component Rules) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 1. Registered Users -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Registered Users</p>
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $totalUsers }}</h3>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-blue-600 dark:text-blue-400 font-semibold">
                <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                <span>Active platform accounts</span>
            </div>
        </div>

        <!-- 2. Global Invoices -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Global Invoices</p>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $totalInvoices }}</h3>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-semibold">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                <span>Issued across all users</span>
            </div>
        </div>

        <!-- 3. Platform Revenue -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Platform Revenue</p>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-2">${{ number_format($totalRevenue, 2) }}</h3>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400 font-semibold">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                <span>Settled Stripe volume</span>
            </div>
        </div>

        <!-- 4. Active Plans -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Active Plans</p>
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 dark:text-amber-400 flex items-center justify-center shrink-0">
                    <i data-lucide="package" class="w-5 h-5"></i>
                </div>
            </div>
            <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $activePackagesCount }}</h3>
            <div class="mt-2 flex items-center gap-1.5 text-xs text-amber-500 dark:text-amber-400 font-semibold">
                <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                <span>Published subscription tiers</span>
            </div>
        </div>
    </div>

    <!-- 2 Column Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Table 1: Recent Stripe Transactions -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Recent Stripe Billings</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Payment receipts and subscription upgrades</p>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-full border border-emerald-200 dark:border-emerald-800/60">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Live Stripe Sync
                </span>
            </div>

            @if($recentTransactions->isEmpty())
                <div class="p-8 text-center text-slate-400 text-xs">No billing transactions recorded yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Package</th>
                                <th class="px-6 py-3">Amount</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($recentTransactions as $tx)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($tx->user->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $tx->user->name }}</div>
                                            <div class="text-[10px] text-slate-400">{{ $tx->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $tx->package->name ?? 'Custom Plan' }}
                                </td>
                                <td class="px-6 py-3.5 font-extrabold text-slate-900 dark:text-white text-xs">
                                    ${{ number_format($tx->amount, 2) }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $tx->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Table 2: Recent Registrations -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Recent Registrations</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Latest platform accounts and roles</p>
                </div>
                <a href="{{ route('admin.users') }}" class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline flex items-center gap-1">
                    Manage All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3">User</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Credits</th>
                            <th class="px-6 py-3">Plan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($recentUsers as $u)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xs">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $u->name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 font-black text-slate-900 dark:text-white text-xs">
                                {{ $u->invoice_credits >= 9999 ? 'Unlimited' : $u->invoice_credits }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-600 dark:text-slate-300 font-medium">
                                {{ $u->package->name ?? 'Free Tier' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
