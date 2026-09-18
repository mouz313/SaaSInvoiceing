@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    <!-- Top Greeting & Credits Summary Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-slate-900 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/15 text-white backdrop-blur-xs mb-2">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-300"></i>
                    InvoiceHub SaaS Engine
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Welcome back, {{ $user->name }}!</h2>
                <p class="text-blue-100 text-sm mt-1 max-w-xl">
                    Create invoices, manage client billing profiles, and switch between 4 custom styles.
                </p>
            </div>

            <div class="flex items-center gap-3 self-start md:self-auto">
                <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-blue-600 font-bold text-sm shadow-md hover:bg-blue-50 transition transform hover:-translate-y-0.5">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Create Invoice
                </a>
                <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/20 hover:bg-white/30 text-white font-semibold text-sm backdrop-blur-xs transition">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    New Client
                </a>
            </div>
        </div>
        <!-- Decorative subtle background shape -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 rounded-full bg-white/5 pointer-events-none"></div>
    </div>

    <!-- KPI Widgets (§3 Stat Cards / KPI Widgets) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Invoices -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Invoices</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $totalInvoices }}</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400 mt-1 inline-block">Generated to date</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 2: Total Paid -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Paid Revenue</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">${{ number_format($totalPaid, 2) }}</h3>
                <span class="text-xs text-emerald-600/80 font-medium mt-1 inline-block flex items-center gap-1">
                    <i data-lucide="arrow-up-right" class="w-3.5 h-3.5"></i> Settled payments
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 3: Pending Balance -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Pending Amount</p>
                <h3 class="text-2xl sm:text-3xl font-extrabold text-amber-500 dark:text-amber-400 mt-1">${{ number_format($totalPending, 2) }}</h3>
                <span class="text-xs text-slate-500 dark:text-slate-400 mt-1 inline-block">Draft & Sent invoices</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-500 dark:text-amber-400 flex items-center justify-center shrink-0">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 4: Invoice Credits -->
        <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Plan & Credits</p>
                @if($user->package && $user->package->invoice_limit === -1)
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">Unlimited</h3>
                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-1 inline-block">{{ $user->package->name }} Plan</span>
                @else
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-blue-600 dark:text-blue-400 mt-1">{{ $user->invoice_credits }}</h3>
                    <a href="{{ route('home') }}#pricing" class="text-xs text-blue-600 dark:text-blue-400 hover:underline font-semibold mt-1 inline-block">
                        Top up credits &rarr;
                    </a>
                @endif
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Two Column Section: Recent Invoices & Recent Clients -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Invoices Table (2 cols) -->
        <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Recent Invoices</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Latest activity across your accounts</p>
                </div>
                <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    View All <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if($recentInvoices->isEmpty())
                <!-- Designed Empty State (§2 Requirement) -->
                <div class="p-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center mb-3">
                        <i data-lucide="receipt" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm">No invoices created yet</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Create your first invoice and choose from 4 styles.</p>
                    <a href="{{ route('invoices.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Create First Invoice
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                            <tr>
                                <th class="px-6 py-3">Number</th>
                                <th class="px-6 py-3">Client</th>
                                <th class="px-6 py-3">Due Date</th>
                                <th class="px-6 py-3">Amount</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach($recentInvoices as $invoice)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="px-6 py-4 font-bold text-blue-600 dark:text-blue-400">
                                    <a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                </td>
                                <td class="px-6 py-4 text-slate-900 dark:text-slate-200">
                                    <div class="font-medium">{{ $invoice->client->name }}</div>
                                    @if($invoice->client->company_name)
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->client->company_name }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">
                                    {{ $invoice->due_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                    {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($invoice->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                            Paid
                                        </span>
                                    @elseif($invoice->status === 'sent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400">
                                            Sent
                                        </span>
                                    @elseif($invoice->status === 'overdue')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400">
                                            Overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300">
                                            Draft
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 inline-block transition">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Recent Clients (1 col) -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Recent Clients</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Active customer profiles</p>
                </div>
                <a href="{{ route('clients.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    View All
                </a>
            </div>

            <div class="p-6 flex-1 flex flex-col justify-between">
                @if($recentClients->isEmpty())
                    <div class="text-center py-8">
                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center mb-2">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">No clients added yet</p>
                        <a href="{{ route('clients.create') }}" class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:underline">
                            + Add Client
                        </a>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($recentClients as $client)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($client->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $client->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $client->company_name ?: $client->email }}</p>
                                </div>
                            </div>
                            <a href="{{ route('clients.edit', $client) }}" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('clients.create') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:border-blue-600 hover:text-blue-600 transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Add Another Client
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
