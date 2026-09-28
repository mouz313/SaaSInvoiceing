@extends('layouts.app')

@section('title', 'Bank Import & Accounting Sync')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto" x-data="{ uploadModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-950 dark:text-white tracking-tight flex items-center gap-2">
                <i data-lucide="landmark" class="w-7 h-7 text-emerald-600"></i>
                Bank Import & Accounting Sync
            </h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                Upload CSV bank statements from Pakistani banks (Meezan, HBL, Alfalah) and automatically reconcile incoming deposits against unpaid invoices.
            </p>
        </div>
        <div>
            <button @click="uploadModal = true" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm shadow-emerald-500/20 inline-flex items-center gap-1.5 transition">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                Upload Statement CSV
            </button>
        </div>
    </div>

    <!-- Quick Metrics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider block">Total Imported</span>
            <span class="text-2xl font-black text-slate-950 dark:text-white mt-1 block">{{ number_format($stats['total_imported']) }}</span>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs text-amber-600 dark:text-amber-400 font-bold uppercase tracking-wider block">Pending Match</span>
            <span class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1 block">{{ number_format($stats['unreconciled']) }}</span>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider block">Reconciled / Settled</span>
            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 block">{{ number_format($stats['matched']) }}</span>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs">
            <span class="text-xs text-blue-600 dark:text-blue-400 font-bold uppercase tracking-wider block">Total Credits (PKR)</span>
            <span class="text-xl font-black text-slate-950 dark:text-white mt-1 block">{{ number_format((float) $stats['total_credit'], 2) }}</span>
        </div>
    </div>

    <!-- Filter Pills -->
    <div class="flex items-center gap-2">
        <a href="{{ route('bank-sync.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ !request()->query('filter') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
            All Transactions
        </a>
        <a href="{{ route('bank-sync.index', ['filter' => 'unreconciled']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->query('filter') === 'unreconciled' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
            Pending Match
        </a>
        <a href="{{ route('bank-sync.index', ['filter' => 'matched']) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ request()->query('filter') === 'matched' ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
            Reconciled
        </a>
    </div>

    <!-- Bank Transactions Ledger -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3 px-6">Date</th>
                        <th class="py-3 px-6">Bank / Source</th>
                        <th class="py-3 px-6">Narration / Description</th>
                        <th class="py-3 px-6">Ref / TRX</th>
                        <th class="py-3 px-6">Amount (PKR)</th>
                        <th class="py-3 px-6">Reconciliation Target</th>
                        <th class="py-3 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition">
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $trx->transaction_date->format('d M, Y') }}</td>
                            <td class="py-4 px-6">
                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                    {{ $trx->bank_name }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-medium text-slate-900 dark:text-white max-w-xs truncate" title="{{ $trx->description }}">
                                {{ $trx->description }}
                            </td>
                            <td class="py-4 px-6 font-mono text-slate-500">{{ $trx->reference_number ?: '—' }}</td>
                            <td class="py-4 px-6 font-bold {{ $trx->type === 'credit' ? 'text-emerald-600' : 'text-slate-500' }}">
                                {{ $trx->type === 'credit' ? '+' : '-' }}PKR {{ number_format((float) $trx->amount, 2) }}
                            </td>
                            <td class="py-4 px-6">
                                @if($trx->status === 'matched' && $trx->matchedInvoice)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 font-bold text-[10px]">
                                        <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i>
                                        Matched: #{{ $trx->matchedInvoice->invoice_number }}
                                    </span>
                                @elseif($trx->matchedInvoice)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 font-bold text-[10px]">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-blue-600"></i>
                                        Suggested: #{{ $trx->matchedInvoice->invoice_number }} ({{ $trx->matchedInvoice->client->name }})
                                    </span>
                                @elseif($trx->status === 'ignored')
                                    <span class="text-slate-400 italic">Ignored</span>
                                @else
                                    <span class="text-amber-600 text-[11px] font-medium">Unmatched</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                @if($trx->status === 'unreconciled')
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($trx->matchedInvoice)
                                            <form method="POST" action="{{ route('bank-sync.reconcile', $trx) }}">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition">
                                                    Reconcile
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('bank-sync.reconcile', $trx) }}" class="inline-flex items-center gap-1">
                                                @csrf
                                                <select name="invoice_id" required class="px-2 py-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-[10px]">
                                                    <option value="">Select Invoice...</option>
                                                    @foreach($unpaidInvoices as $inv)
                                                        <option value="{{ $inv->id }}">#{{ $inv->invoice_number }} (PKR {{ number_format($inv->balance_due, 0) }})</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="px-2 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold transition">
                                                    Link
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('bank-sync.ignore', $trx) }}">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 text-slate-400 hover:text-slate-600 font-bold transition" title="Ignore">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 px-6 text-center text-slate-500">
                                <i data-lucide="receipt-text" class="w-8 h-8 text-slate-400 mx-auto mb-2 opacity-50"></i>
                                No bank transactions imported yet. Click "Upload Statement CSV" to import bank activity.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Upload Statement Modal -->
    <div x-show="uploadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 border border-slate-200 dark:border-slate-800 shadow-xl" @click.outside="uploadModal = false">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">Import Bank Statement (CSV)</h3>
            <p class="text-xs text-slate-500">Supports exported CSV statements from Pakistani and global banking portals.</p>

            <form method="POST" action="{{ route('bank-sync.upload') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Bank Format</label>
                    <select name="bank_name" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm">
                        <option value="meezan">Meezan Bank (Internet Banking CSV)</option>
                        <option value="hbl">Habib Bank Limited (HBL Web CSV)</option>
                        <option value="alfalah">Bank Alfalah (Alfa CSV)</option>
                        <option value="generic">Standard / Generic CSV (Date, Narration, Ref, Credit/Debit)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Statement CSV File</label>
                    <input type="file" name="statement_file" accept=".csv,.txt" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-950/60 dark:file:text-blue-300">
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl text-[11px] text-slate-600 dark:text-slate-400 space-y-1">
                    <span class="font-bold text-slate-900 dark:text-white block">💡 Smart Reconciliation Match:</span>
                    <span>The system matches incoming deposits automatically against invoice numbers and client balances. You can review and settle in 1-click.</span>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="uploadModal = false" class="px-4 py-2 rounded-xl text-slate-600 dark:text-slate-400 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                        Upload & Reconcile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
