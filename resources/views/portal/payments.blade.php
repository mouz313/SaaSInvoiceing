@extends('portal.layout')

@section('title', 'Payment History')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900">Payment History</h1>
        <p class="text-xs text-slate-500 mt-0.5">Official transaction ledger of all payments and credits</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Payment Date</th>
                        <th class="py-3.5 px-4">Invoice #</th>
                        <th class="py-3.5 px-4">Payment Method</th>
                        <th class="py-3.5 px-4">Transaction / Ref #</th>
                        <th class="py-3.5 px-4">Notes</th>
                        <th class="py-3.5 px-4 text-right">Amount Paid</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 text-slate-700 font-semibold whitespace-nowrap">
                                {{ $payment->paid_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                @if($payment->invoice)
                                    <a href="{{ $payment->invoice->public_url }}" target="_blank" class="text-blue-600 hover:underline">
                                        #{{ $payment->invoice->invoice_number }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 capitalize">
                                {{ str_replace('_', ' ', $payment->payment_method ?? 'Payment') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-500">
                                {{ $payment->reference_number ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                {{ $payment->notes ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-black text-emerald-600">
                                {{ \App\Support\Currency::format($payment->amount, $payment->invoice?->currency ?? $portalClient->currency) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                No payment records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection