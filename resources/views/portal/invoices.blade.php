@extends('portal.layout')

@section('title', 'My Invoices')

@section('content')
<div class="space-y-6">
    <!-- Header & Filter Pills -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900">Invoices</h1>
            <p class="text-xs text-slate-500 mt-0.5">View and settle all billing invoices issued to your company</p>
        </div>

        <div class="flex items-center gap-1.5 bg-slate-200/70 p-1 rounded-xl">
            <a href="{{ route('portal.invoices', ['status' => 'all']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                All
            </a>
            <a href="{{ route('portal.invoices', ['status' => 'unpaid']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'unpaid' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Unpaid
            </a>
            <a href="{{ route('portal.invoices', ['status' => 'paid']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'paid' ? 'bg-white text-emerald-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Paid
            </a>
            <a href="{{ route('portal.invoices', ['status' => 'overdue']) }}" 
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $status === 'overdue' ? 'bg-white text-rose-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Overdue
            </a>
        </div>
    </div>

    <!-- Invoices Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Invoice #</th>
                        <th class="py-3.5 px-4">Issue Date</th>
                        <th class="py-3.5 px-4">Due Date</th>
                        <th class="py-3.5 px-4 text-right">Total</th>
                        <th class="py-3.5 px-4 text-right">Paid</th>
                        <th class="py-3.5 px-4 text-right">Balance Due</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                #{{ $invoice->invoice_number }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $invoice->invoice_date->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                {{ \App\Support\Currency::format($invoice->total, $invoice->currency) }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-emerald-600 font-semibold">
                                {{ \App\Support\Currency::format($invoice->amount_paid ?? 0, $invoice->currency) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold {{ ($invoice->balance_due ?? $invoice->total) > 0 ? 'text-rose-600' : 'text-slate-900' }}">
                                {{ \App\Support\Currency::format($invoice->balance_due ?? $invoice->total, $invoice->currency) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
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
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($invoice->status !== 'paid')
                                        <a href="{{ $invoice->public_url }}" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                            Pay Online
                                        </a>
                                    @endif
                                    <a href="{{ $invoice->public_url }}" target="_blank" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" title="View Invoice">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('invoices.public.pdf', $invoice->public_token) }}" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100" title="Download PDF">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                No invoices matching this filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection