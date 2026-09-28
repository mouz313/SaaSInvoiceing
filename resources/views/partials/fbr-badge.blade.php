@if($invoice->isFbrSynced())
<div class="fbr-fiscal-badge p-3 rounded-xl border border-emerald-300 dark:border-emerald-800 bg-emerald-50/70 dark:bg-emerald-950/40 flex items-center justify-between gap-3 text-left">
    <div class="flex items-center gap-3">
        @if($invoice->fbr_qr_code_data)
            <div class="w-14 h-14 bg-white p-1 rounded-lg border border-emerald-200 shrink-0 flex items-center justify-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($invoice->fbr_qr_code_data) }}" 
                     alt="FBR POS Fiscal QR Code" 
                     class="w-full h-full object-contain">
            </div>
        @endif
        <div>
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-emerald-700 text-white">FBR VERIFIED</span>
                <span class="text-[11px] font-bold text-emerald-900 dark:text-emerald-200">Electronic Billing System (EBS)</span>
            </div>
            <div class="text-[11px] text-slate-600 dark:text-slate-400 font-mono mt-0.5">
                FBR POS Invoice #: <strong class="text-slate-900 dark:text-white">{{ $invoice->fbr_invoice_number }}</strong>
            </div>
            <div class="text-[9px] text-slate-500 dark:text-slate-400">
                POS ID: {{ $invoice->user->fbr_pos_id }} &bull; Fiscalized on: {{ $invoice->fbr_synced_at?->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
    <div class="text-right hidden sm:block">
        <span class="text-[9px] uppercase font-bold text-emerald-800 dark:text-emerald-300 block">Sales Tax Act 1990</span>
        <span class="text-[9px] text-slate-500">Government of Pakistan</span>
    </div>
</div>
@endif
