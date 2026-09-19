@extends('portal.layout')

@section('title', 'Account Statement')

@section('content')
<div class="space-y-6">
    <!-- Header & Date Filter -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900">Statement of Account</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Activity summary from 
                <strong>{{ $statement['startDate'] ? $statement['startDate']->format('M d, Y') : 'Beginning' }}</strong>
                to
                <strong>{{ $statement['endDate'] ? $statement['endDate']->format('M d, Y') : 'Present' }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('portal.statement.pdf', ['range' => $range, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF Statement
            </a>
        </div>
    </div>

    <!-- Date Range Filter Selector -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('portal.statement') }}" class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
            <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Period:</span>
            <a href="{{ route('portal.statement', ['range' => 'last_30_days']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $range === 'last_30_days' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Last 30 Days
            </a>
            <a href="{{ route('portal.statement', ['range' => 'this_month']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $range === 'this_month' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                This Month
            </a>
            <a href="{{ route('portal.statement', ['range' => 'year_to_date']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $range === 'year_to_date' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Year to Date
            </a>
            <a href="{{ route('portal.statement', ['range' => 'all_time']) }}" 
               class="px-3 py-1.5 rounded-lg font-semibold transition {{ $range === 'all_time' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                All Time
            </a>
        </form>
    </div>

    <!-- Statement Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Opening Balance</span>
            <div class="mt-2 text-xl font-bold text-slate-900">
                {{ \App\Support\Currency::format($statement['openingBalance'], $statement['currency']) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Prior to period start</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Invoiced in Period</span>
            <div class="mt-2 text-xl font-bold text-slate-900">
                +{{ \App\Support\Currency::format($statement['totalInvoiced'], $statement['currency']) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">New charges billed</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Paid in Period</span>
            <div class="mt-2 text-xl font-bold text-emerald-600">
                -{{ \App\Support\Currency::format($statement['totalPaid'], $statement['currency']) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Payments settled</div>
        </div>

        <div class="bg-slate-900 text-white rounded-2xl p-4 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Closing Balance Due</span>
            <div class="mt-2 text-xl font-black text-white">
                {{ \App\Support\Currency::format($statement['closingBalance'], $statement['currency']) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">Current net balance</div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 font-bold text-xs uppercase text-slate-500 tracking-wider">
            Transaction Activity Ledger
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Activity / Reference</th>
                        <th class="py-3 px-4">Details</th>
                        <th class="py-3 px-4 text-right">Billed (+)</th>
                        <th class="py-3 px-4 text-right">Paid (-)</th>
                        <th class="py-3 px-4 text-right">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <!-- Opening Row -->
                    <tr class="bg-slate-50/60 font-semibold">
                        <td class="py-3 px-4 text-slate-500">
                            {{ $statement['startDate'] ? $statement['startDate']->format('M d, Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-slate-900" colspan="4">
                            Starting / Opening Balance
                        </td>
                        <td class="py-3 px-4 text-right text-slate-900 font-bold">
                            {{ \App\Support\Currency::format($statement['openingBalance'], $statement['currency']) }}
                        </td>
                    </tr>

                    @forelse($statement['ledger'] as $row)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 text-slate-600 whitespace-nowrap">
                                {{ $row['date']->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                @if($row['type'] === 'invoice')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-700 mr-1.5">INVOICE</span>
                                    <a href="{{ $row['model']->public_url }}" target="_blank" class="text-blue-600 hover:underline">
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
                            <td class="py-3 px-4 text-right font-semibold text-slate-900">
                                {{ $row['debit'] > 0 ? \App\Support\Currency::format($row['debit'], $statement['currency']) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-semibold text-emerald-600">
                                {{ $row['credit'] > 0 ? '-' . \App\Support\Currency::format($row['credit'], $statement['currency']) : '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-black text-slate-900">
                                {{ \App\Support\Currency::format($row['balance'], $statement['currency']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                No activity in this period.
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
</div>
@endsection