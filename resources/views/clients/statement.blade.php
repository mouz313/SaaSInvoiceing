@extends('layouts.app')

@section('title', 'Statement of Account - ' . $client->name)

@section('content')
<div class="space-y-6" x-data="{ emailModal: false }">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('clients.index') }}" class="text-xs text-slate-500 hover:text-slate-700">Clients</a>
                <span class="text-xs text-slate-400">/</span>
                <a href="{{ route('clients.show', $client) }}" class="text-xs text-slate-500 hover:text-slate-700">{{ $client->name }}</a>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-semibold text-slate-900 dark:text-white">Statement</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white mt-1">Statement of Account</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Summary for <strong>{{ $client->name }}</strong>
                ({{ $statement['startDate'] ? $statement['startDate']->format('M d, Y') : 'Beginning' }} &mdash; {{ $statement['endDate'] ? $statement['endDate']->format('M d, Y') : 'Present' }})
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('clients.statement.pdf', ['client' => $client, 'range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>

            @if($client->email)
                <button type="button" @click="emailModal = true"
                        class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email Statement
                </button>
            @endif
        </div>
    </div>

    <!-- Date Range Selector -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-xs">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
            <span class="font-bold text-slate-500 uppercase tracking-wider text-[11px]">Period:</span>
            <a href="{{ route('clients.statement', ['client' => $client, 'range' => 'last_30_days']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $range === 'last_30_days' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                Last 30 Days
            </a>
            <a href="{{ route('clients.statement', ['client' => $client, 'range' => 'this_month']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $range === 'this_month' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                This Month
            </a>
            <a href="{{ route('clients.statement', ['client' => $client, 'range' => 'year_to_date']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $range === 'year_to_date' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                Year to Date
            </a>
            <a href="{{ route('clients.statement', ['client' => $client, 'range' => 'all_time']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $range === 'all_time' ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                All Time
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Opening Balance</span>
            <div class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                {{ \App\Support\Currency::format($statement['openingBalance'], $statement['currency']) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Prior to period start</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Invoiced in Period</span>
            <div class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                +{{ \App\Support\Currency::format($statement['totalInvoiced'], $statement['currency']) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Total charges billed</div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Paid in Period</span>
            <div class="mt-2 text-xl font-bold text-emerald-600">
                -{{ \App\Support\Currency::format($statement['totalPaid'], $statement['currency']) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Payments received</div>
        </div>

        <div class="bg-slate-900 dark:bg-slate-950 text-white rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Closing Balance Due</span>
            <div class="mt-2 text-xl font-black text-white">
                {{ \App\Support\Currency::format($statement['closingBalance'], $statement['currency']) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Current net balance</div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-700 font-bold text-xs uppercase text-slate-500 tracking-wider">
            Transaction Activity Ledger
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100 dark:border-slate-700">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Activity / Reference</th>
                        <th class="py-3 px-4">Details</th>
                        <th class="py-3 px-4 text-right">Billed (+)</th>
                        <th class="py-3 px-4 text-right">Paid (-)</th>
                        <th class="py-3 px-4 text-right">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 font-medium">
                    <!-- Opening Row -->
                    <tr class="bg-slate-50/60 dark:bg-slate-900/20 font-semibold">
                        <td class="py-3 px-4 text-slate-500">
                            {{ $statement['startDate'] ? $statement['startDate']->format('M d, Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-900 dark:text-white" colspan="4">
                            Starting / Opening Balance
                        </td>
                        <td class="py-3 px-4 text-right text-slate-900 dark:text-white font-bold">
                            {{ \App\Support\Currency::format($statement['openingBalance'], $statement['currency']) }}
                        </td>
                    </tr>

                    @forelse($statement['ledger'] as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition">
                            <td class="py-3 px-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $row['date']->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                @if($row['type'] === 'invoice')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-700 mr-1.5">INVOICE</span>
                                    <a href="{{ route('invoices.show', $row['model']) }}" class="text-blue-600 hover:underline">
                                        {{ $row['reference'] }}
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-700 mr-1.5">PAYMENT</span>
                                    {{ $row['reference'] }}
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $row['description'] }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-slate-900 dark:text-white">
                                {{ $row['debit'] > 0 ? \App\Support\Currency::format($row['debit'], $statement['currency']) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-emerald-600">
                                {{ $row['credit'] > 0 ? '-' . \App\Support\Currency::format($row['credit'], $statement['currency']) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-white">
                                {{ \App\Support\Currency::format($row['balance'], $statement['currency']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                No invoice or payment activity in this selected period.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Closing Row -->
                    <tr class="bg-slate-900 text-white font-bold">
                        <td colspan="5" class="py-3.5 px-4 uppercase text-[11px] tracking-wider">
                            Closing Balance Due:
                        </td>
                        <td class="py-3.5 px-4 text-right text-sm font-black">
                            {{ \App\Support\Currency::format($statement['closingBalance'], $statement['currency']) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Email Statement Modal -->
    <div x-show="emailModal" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-xl relative" @click.outside="emailModal = false">
            <h3 class="text-lg font-black text-slate-900 dark:text-white">Email Statement to Client</h3>
            <p class="text-xs text-slate-500 mt-1">Send this statement with attached PDF directly to {{ $client->email }}.</p>

            <form method="POST" action="{{ route('clients.statement.email', $client) }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="range" value="{{ $range }}">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Recipient Email
                    </label>
                    <input type="email" value="{{ $client->email }}" disabled class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-100 dark:bg-slate-900 text-xs text-slate-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Custom Note (Optional)
                    </label>
                    <textarea name="custom_message" rows="3" placeholder="Please find your latest account statement attached for review..." class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-600 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="emailModal = false" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                        Send Email Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection