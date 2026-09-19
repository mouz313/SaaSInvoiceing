@extends('layouts.app')

@section('title', 'Client Profile - ' . $client->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('clients.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Clients</a>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-900 dark:text-white">{{ $client->name }}</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $client->name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $client->company_name ?: 'Individual Client' }} &bull; Client since {{ $client->created_at->format('M Y') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('clients.statement', $client) }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Account Statement
            </a>
            <a href="{{ route('clients.edit', $client) }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                Edit Client
            </a>
        </div>
    </div>

    <!-- Client Portal Magic Link Card -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-5 text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4" x-data="{ copied: false }">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded bg-blue-500/40 text-[10px] font-extrabold uppercase tracking-wider">Client Portal</span>
                <span class="text-xs text-blue-100 font-medium">Passwordless Magic Access Link</span>
            </div>
            <p class="text-xs text-blue-100">
                Share this private link with {{ $client->name }} so they can review invoices, proposals, and pay balances online.
            </p>
            <div class="pt-1 text-[11px] font-mono text-blue-200 truncate max-w-xl">
                {{ $client->portal_url }}
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button" @click="navigator.clipboard.writeText('{{ $client->portal_url }}'); copied = true; setTimeout(() => copied = false, 2500)"
                    class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition flex items-center gap-1.5 backdrop-blur-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                <span x-text="copied ? 'Copied!' : 'Copy Portal Link'"></span>
            </button>

            @if($client->email)
                <form method="POST" action="{{ route('clients.portal-link', $client) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-white text-blue-700 hover:bg-blue-50 text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Email Link
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Invoiced</span>
            <div class="mt-2 text-2xl font-black text-slate-900 dark:text-white">
                {{ \App\Support\Currency::format($client->totalInvoiced(), $client->currency) }}
            </div>
            <div class="mt-1 text-xs text-slate-400">{{ $client->invoices()->count() }} total invoices</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Paid</span>
            <div class="mt-2 text-2xl font-black text-emerald-600">
                {{ \App\Support\Currency::format($client->totalPaid(), $client->currency) }}
            </div>
            <div class="mt-1 text-xs text-slate-400">Settled payments</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Outstanding Balance</span>
            <div class="mt-2 text-2xl font-black {{ $client->totalOutstanding() > 0 ? 'text-rose-600' : 'text-slate-900 dark:text-white' }}">
                {{ \App\Support\Currency::format($client->totalOutstanding(), $client->currency) }}
            </div>
            <div class="mt-1 text-xs text-slate-400">Pending settlement</div>
        </div>
    </div>

    <!-- Invoices & Estimates Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Invoices -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300">Recent Invoices</h3>
                <a href="{{ route('invoices.create', ['client_id' => $client->id]) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                    + New Invoice
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-400 font-bold text-[10px] uppercase">
                        <tr>
                            <th class="py-2.5 px-4">Invoice #</th>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4 text-right">Total</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 font-medium">
                        @forelse($client->invoices as $inv)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="py-2.5 px-4 font-bold">
                                    <a href="{{ route('invoices.show', $inv) }}" class="text-blue-600 hover:underline">
                                        #{{ $inv->invoice_number }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-4 text-slate-500">{{ $inv->invoice_date->format('M d, Y') }}</td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-900 dark:text-white">{{ \App\Support\Currency::format($inv->total, $inv->currency) }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-{{ $inv->status_color }}-100 text-{{ $inv->status_color }}-700">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 text-xs">No invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Estimates -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-300">Estimates &amp; Quotes</h3>
                <a href="{{ route('estimates.create', ['client_id' => $client->id]) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                    + New Estimate
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-400 font-bold text-[10px] uppercase">
                        <tr>
                            <th class="py-2.5 px-4">Estimate #</th>
                            <th class="py-2.5 px-4">Date</th>
                            <th class="py-2.5 px-4 text-right">Total</th>
                            <th class="py-2.5 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700 font-medium">
                        @forelse($client->estimates as $est)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="py-2.5 px-4 font-bold">
                                    <a href="{{ route('estimates.show', $est) }}" class="text-blue-600 hover:underline">
                                        #{{ $est->estimate_number }}
                                    </a>
                                </td>
                                <td class="py-2.5 px-4 text-slate-500">{{ $est->estimate_date->format('M d, Y') }}</td>
                                <td class="py-2.5 px-4 text-right font-bold text-slate-900 dark:text-white">{{ \App\Support\Currency::format($est->total, $est->currency) }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-{{ $est->status_color }}-100 text-{{ $est->status_color }}-700">
                                        {{ $est->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400 text-xs">No estimates created.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection