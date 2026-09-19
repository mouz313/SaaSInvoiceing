@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">Invoices Registry</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Track, manage, and download PDF invoices</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Create Invoice
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Status Filter Pills (§3 Form & Filters) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('invoices.index') }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ !$status ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                All ({{ Auth::user()->invoices()->count() }})
            </a>
            <a href="{{ route('invoices.index', ['status' => 'paid']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'paid' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Paid
            </a>
            <a href="{{ route('invoices.index', ['status' => 'sent']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'sent' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Sent
            </a>
            <a href="{{ route('invoices.index', ['status' => 'draft']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'draft' ? 'bg-slate-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Draft
            </a>
            <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'overdue' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Overdue
            </a>
        </div>

        <form method="GET" action="{{ route('invoices.index') }}" class="relative">
            @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Search invoices or clients..." 
                   class="w-full sm:w-64 pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-blue-600">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
        </form>
    </div>

    <!-- Invoices Table Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        @if($invoices->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="receipt" class="w-6 h-6"></i>
                </div>
                <h4 class="font-bold text-slate-800 dark:text-slate-200 text-base">No invoices found</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    @if($search || $status)
                        No invoices match your current filters.
                    @else
                        Create your first invoice with automated calculations and 4 layout styles.
                    @endif
                </p>
                <a href="{{ route('invoices.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> New Invoice
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-6 py-3.5">Invoice #</th>
                            <th class="px-6 py-3.5">Client</th>
                            <th class="px-6 py-3.5">Style</th>
                            <th class="px-6 py-3.5">Issue Date</th>
                            <th class="px-6 py-3.5">Due Date</th>
                            <th class="px-6 py-3.5">Total</th>
                            <th class="px-6 py-3.5">Status</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach($invoices as $invoice)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-extrabold text-blue-600 dark:text-blue-400 hover:underline">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $invoice->client->name }}</div>
                                @if($invoice->client->company_name)
                                    <div class="text-xs text-slate-400">{{ $invoice->client->company_name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 capitalize">
                                    {{ $invoice->style }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">
                                {{ $invoice->invoice_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">
                                {{ $invoice->due_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900 dark:text-white">
                                    {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                                </div>
                                @if(($invoice->amount_paid ?? 0) > 0 && !$invoice->isPaid())
                                    <div class="text-[11px] text-emerald-600 font-semibold">Bal: {{ $invoice->currency }} {{ number_format($invoice->balance_due ?? 0, 2) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($invoice->status === 'paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                        Paid
                                    </span>
                                @elseif($invoice->status === 'partially_paid')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400">
                                        Partially Paid
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
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('invoices.public', $invoice->public_token) }}" target="_blank" title="Client Portal &amp; Pay Link" class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition">
                                        <i data-lucide="globe" class="w-4 h-4"></i>
                                    </a>
                                    @php
                                        $waListText = urlencode("Hello {$invoice->client->name}, here is your invoice #{$invoice->invoice_number} for {$invoice->currency} " . number_format($invoice->total, 2) . ". View and pay online here: " . $invoice->public_url);
                                        $waPhoneList = preg_replace('/[^0-9]/', '', $invoice->client->phone ?? '');
                                    @endphp
                                    <a href="https://wa.me/{{ $waPhoneList }}?text={{ $waListText }}" target="_blank" title="Share via WhatsApp" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition">
                                        <i data-lucide="message-circle" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('invoices.show', $invoice) }}" title="View &amp; Change Style" class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('invoices.pdf', $invoice) }}" title="Download PDF" class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('invoices.edit', $invoice) }}" title="Edit Invoice" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Delete" class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
