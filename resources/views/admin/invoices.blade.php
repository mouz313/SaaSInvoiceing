@extends('layouts.admin')

@section('title', 'Global Platform Invoices')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">System-Wide Invoices</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Audit all invoices generated across all client accounts</p>
        </div>

        <form method="GET" action="{{ route('admin.invoices') }}" class="relative">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search invoice # or user..." 
                   class="w-full sm:w-64 pl-9 pr-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-600 focus:outline-none shadow-xs">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
        </form>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3.5">Invoice #</th>
                        <th class="px-6 py-3.5">Creator User</th>
                        <th class="px-6 py-3.5">Billed Client</th>
                        <th class="px-6 py-3.5">Style</th>
                        <th class="px-6 py-3.5">Amount</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Inspect</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="px-6 py-4 font-bold text-blue-600 dark:text-blue-400">
                            {{ $inv->invoice_number }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $inv->user->name }}</div>
                            <div class="text-[10px] text-slate-400">{{ $inv->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300 text-xs">
                            {{ $inv->client->name }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-mono uppercase bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                {{ $inv->style }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white">
                            {{ $inv->currency }} {{ number_format($inv->total, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($inv->status === 'paid')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                    Paid
                                </span>
                            @elseif($inv->status === 'sent')
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400">
                                    Sent
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ $inv->status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('invoices.show', $inv) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 inline-block transition">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 text-xs">No invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection
