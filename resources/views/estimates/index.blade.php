@extends('layouts.app')

@section('title', 'Quotations & Estimates')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Search Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2.5">
                <i data-lucide="file-text" class="w-6 h-6 text-indigo-600 dark:text-indigo-400"></i>
                Quotations &amp; Proposals
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Create commercial proposals, get client approvals, and convert to invoices in 1 click</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('estimates.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Create Quotation
            </a>
        </div>
    </div>

    <!-- Quick Stats Overview -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Total Quotes</span>
            <span class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1 block">{{ $stats['total'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Pending Review</span>
            <span class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 block">{{ $stats['pending'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">Accepted Proposals</span>
            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block">{{ $stats['accepted'] }}</span>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400 block">Invoiced / Won</span>
            <span class="text-2xl font-extrabold text-purple-600 dark:text-purple-400 mt-1 block">{{ $stats['invoiced'] }}</span>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Status Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0">
            <a href="{{ route('estimates.index') }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ !$status ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                All ({{ $stats['total'] }})
            </a>
            <a href="{{ route('estimates.index', ['status' => 'draft']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'draft' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Draft
            </a>
            <a href="{{ route('estimates.index', ['status' => 'sent']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'sent' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Sent
            </a>
            <a href="{{ route('estimates.index', ['status' => 'accepted']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'accepted' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Accepted
            </a>
            <a href="{{ route('estimates.index', ['status' => 'invoiced']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'invoiced' ? 'bg-purple-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Invoiced
            </a>
            <a href="{{ route('estimates.index', ['status' => 'declined']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition {{ $status === 'declined' ? 'bg-rose-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                Declined
            </a>
        </div>

        <form method="GET" action="{{ route('estimates.index') }}" class="relative">
            @if($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Search estimates or clients..." 
                   class="w-full sm:w-64 pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-600">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
        </form>
    </div>

    <!-- Estimates Table Card -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        @if($estimates->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <i data-lucide="file-question" class="w-6 h-6"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">No quotations found</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    Create your first quotation proposal to send pricing and deliverables to clients before invoicing.
                </p>
                <div class="mt-4">
                    <a href="{{ route('estimates.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white font-semibold text-xs hover:bg-indigo-700 transition">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        New Quotation
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="py-3.5 px-4 sm:px-6">Quote #</th>
                            <th class="py-3.5 px-4">Client</th>
                            <th class="py-3.5 px-4">Quote Date</th>
                            <th class="py-3.5 px-4">Valid Until</th>
                            <th class="py-3.5 px-4">Amount</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 sm:px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                        @foreach($estimates as $estimate)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="py-4 px-4 sm:px-6 font-bold text-slate-900 dark:text-white">
                                    <a href="{{ route('estimates.show', $estimate) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $estimate->estimate_number }}
                                    </a>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $estimate->client->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $estimate->client->company_name ?: $estimate->client->email }}</div>
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400">{{ $estimate->estimate_date->format('M d, Y') }}</td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400">{{ $estimate->expiry_date->format('M d, Y') }}</td>
                                <td class="py-4 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ $estimate->currency }} {{ number_format($estimate->total, 2) }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-{{ $estimate->status_color }}-100 dark:bg-{{ $estimate->status_color }}-950/60 text-{{ $estimate->status_color }}-700 dark:text-{{ $estimate->status_color }}-400">
                                        {{ $estimate->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- 1-Click Convert to Invoice Button --}}
                                        @if(! $estimate->isInvoiced())
                                            <form action="{{ route('estimates.convert', $estimate) }}" method="POST" onsubmit="return confirm('Convert this estimate into an active Invoice? (Consumes 1 credit)');" class="inline">
                                                @csrf
                                                <button type="submit" title="1-Click Convert to Invoice" class="p-1.5 rounded-lg text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition">
                                                    <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('invoices.show', $estimate->converted_invoice_id) }}" title="View Converted Invoice" class="p-1.5 rounded-lg text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-950/40 transition">
                                                <i data-lucide="receipt" class="w-4 h-4"></i>
                                            </a>
                                        @endif

                                        {{-- WhatsApp Share --}}
                                        @php
                                            $waText = urlencode("Hello {$estimate->client->name}, please review your quotation proposal #{$estimate->estimate_number} for {$estimate->currency} " . number_format($estimate->total, 2) . ". View and accept online here: " . $estimate->public_url);
                                            $waPhone = preg_replace('/[^0-9]/', '', $estimate->client->phone ?? '');
                                        @endphp
                                        <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" title="Share via WhatsApp" class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 transition">
                                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                                        </a>

                                        {{-- PDF Download --}}
                                        <a href="{{ route('estimates.pdf', $estimate) }}" title="Download PDF" class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </a>

                                        {{-- View Details --}}
                                        <a href="{{ route('estimates.show', $estimate) }}" title="View Estimate" class="p-1.5 rounded-lg text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 transition">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $estimates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
