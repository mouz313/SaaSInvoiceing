@extends('portal.layout')

@section('title', 'Estimates & Proposals')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900">Estimates &amp; Proposals</h1>
        <p class="text-xs text-slate-500 mt-0.5">Review, accept, or decline project proposals and cost estimates</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 uppercase font-bold text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-4">Estimate #</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4">Valid Until</th>
                        <th class="py-3.5 px-4 text-right">Total Amount</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($estimates as $estimate)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                #{{ $estimate->estimate_number }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $estimate->estimate_date->format('M d, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $estimate->expiry_date ? $estimate->expiry_date->format('M d, Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">
                                {{ \App\Support\Currency::format($estimate->total, $estimate->currency) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($estimate->status === 'accepted')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">ACCEPTED</span>
                                @elseif($estimate->status === 'invoiced')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">INVOICED</span>
                                @elseif($estimate->status === 'declined')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">DECLINED</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">PENDING</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ $estimate->public_url }}" target="_blank" class="px-2.5 py-1 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-semibold transition">
                                        View
                                    </a>

                                    @if($estimate->status === 'sent')
                                        <form method="POST" action="{{ route('portal.estimates.accept', $estimate) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                                Accept
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('portal.estimates.decline', $estimate) }}" class="inline" onsubmit="return confirm('Are you sure you want to decline this estimate?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition">
                                                Decline
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                No estimates or proposals found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($estimates->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $estimates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection