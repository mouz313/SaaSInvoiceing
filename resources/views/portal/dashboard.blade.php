@extends('portal.layout')

@section('title', 'Client Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-500/30 text-blue-200 text-xs font-semibold uppercase tracking-wider mb-3">
                Account Overview
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Welcome back, {{ $portalClient->name }}
            </h1>
            <p class="text-xs sm:text-sm text-blue-100 mt-2 leading-relaxed">
                Manage your billing statements, view incoming estimates, pay outstanding balances, and access your official receipts.
            </p>
        </div>
    </div>

    <!-- Outstanding Balance Alert -->
    @if($outstandingBalance > 0)
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 sm:p-5 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-900">Outstanding Balance Due</h4>
                    <p class="text-xs text-amber-700">You currently have {{ \App\Support\Currency::format($outstandingBalance, $portalClient->currency) }} pending in unpaid invoices.</p>
                </div>
            </div>
            <a href="{{ route('portal.invoices', ['status' => 'unpaid']) }}" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-sm text-center shrink-0">
                View Unpaid Invoices &rarr;
            </a>
        </div>
    @endif

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Outstanding Balance -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Balance Due</span>
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black {{ $outstandingBalance > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                {{ \App\Support\Currency::format($outstandingBalance, $portalClient->currency) }}
            </div>
            <div class="mt-1 text-[11px] text-slate-400">Total unpaid balance</div>
        </div>

        <!-- Total Paid -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Paid</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black text-emerald-600">
                {{ \App\Support\Currency::format($totalPaid, $portalClient->currency) }}
            </div>
            <div class="mt-1 text-[11px] text-slate-400">Total settled to date</div>
        </div>

        <!-- Invoices Issued -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Invoices</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black text-slate-900">
                {{ $invoicesCount }}
            </div>
            <div class="mt-1 text-[11px] text-slate-400">Total invoices on record</div>
        </div>

        <!-- Pending Estimates -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pending Quotes</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </span>
            </div>
            <div class="mt-3 text-2xl font-black text-purple-600">
                {{ $pendingEstimatesCount }}
            </div>
            <div class="mt-1 text-[11px] text-slate-400">Awaiting your review</div>
        </div>
    </div>

    <!-- Main Grid: Recent Invoices & Pending Proposals -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Invoices (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Recent Invoices</h3>
                    <p class="text-xs text-slate-400">Latest invoices issued to your account</p>
                </div>
                <a href="{{ route('portal.invoices') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                    View All &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Due Date</th>
                            <th class="py-3 px-4 text-right">Balance Due</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($recentInvoices as $invoice)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    #{{ $invoice->invoice_number }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $invoice->invoice_date->format('M d, Y') }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    {{ \App\Support\Currency::format($invoice->balance_due ?? $invoice->total, $invoice->currency) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($invoice->status === 'paid')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">PAID</span>
                                    @elseif($invoice->status === 'partially_paid')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">PARTIAL</span>
                                    @elseif($invoice->status === 'overdue' || ($invoice->due_date && $invoice->due_date->isPast()))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">OVERDUE</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">UNPAID</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($invoice->status !== 'paid')
                                            <a href="{{ $invoice->public_url }}" target="_blank" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-[11px] font-bold shadow-xs">
                                                Pay Now
                                            </a>
                                        @endif
                                        <a href="{{ route('invoices.public.pdf', $invoice->public_token) }}" class="p-1 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" title="Download PDF">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                                    No invoices have been issued yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pending Proposals / Quotes (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5">
            <div class="border-b border-slate-100 pb-3 mb-4 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Proposals &amp; Quotes</h3>
                    <p class="text-xs text-slate-400">Quotes awaiting your response</p>
                </div>
                <a href="{{ route('portal.estimates') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                    All &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @forelse($pendingEstimates as $estimate)
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="font-bold text-xs text-slate-900">#{{ $estimate->estimate_number }}</span>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $estimate->estimate_date->format('M d, Y') }}</div>
                            </div>
                            <span class="font-extrabold text-xs text-blue-600">
                                {{ \App\Support\Currency::format($estimate->total, $estimate->currency) }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center gap-2">
                            <a href="{{ $estimate->public_url }}" target="_blank" class="flex-1 text-center py-1.5 px-2.5 rounded-lg bg-blue-600 text-white text-[11px] font-bold hover:bg-blue-700 transition">
                                Review &amp; Accept
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400 text-xs">
                        No pending proposals at this time.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Payments History -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Recent Payments</h3>
                <p class="text-xs text-slate-400">Your recent transaction settlements</p>
            </div>
            <a href="{{ route('portal.payments') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">
                View All &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Payment Date</th>
                        <th class="py-3 px-4">Invoice Ref</th>
                        <th class="py-3 px-4">Payment Method</th>
                        <th class="py-3 px-4">Transaction Ref</th>
                        <th class="py-3 px-4 text-right">Amount Paid</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($recentPayments as $payment)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 text-slate-600">
                                {{ $payment->paid_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                @if($payment->invoice)
                                    <a href="{{ $payment->invoice->public_url }}" target="_blank" class="text-blue-600 hover:underline">
                                        #{{ $payment->invoice->invoice_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-600 capitalize">
                                {{ str_replace('_', ' ', $payment->payment_method ?? 'Online') }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                                {{ $payment->reference_number ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-600">
                                {{ \App\Support\Currency::format($payment->amount, $payment->invoice?->currency ?? $portalClient->currency) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400 text-xs">
                                No payment records logged yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection