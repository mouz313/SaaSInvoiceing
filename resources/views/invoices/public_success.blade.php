<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Successful — Invoice #{{ $invoice->invoice_number }}</title>

    @include('partials.head-assets')
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-full flex items-center justify-center p-4 antialiased">

    <div class="max-w-md w-full bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-200 dark:border-slate-800 shadow-xl shadow-slate-300/30 dark:shadow-black/40 text-center space-y-6">
        <!-- Success Icon with Pulse -->
        <div class="relative w-20 h-20 mx-auto">
            <div class="absolute inset-0 rounded-full bg-emerald-100 dark:bg-emerald-950/60 animate-ping opacity-75"></div>
            <div class="relative w-20 h-20 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <i data-lucide="check" class="w-10 h-10 stroke-[3]"></i>
            </div>
        </div>

        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 mb-2">
                Payment Confirmed
            </span>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Thank You!</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Your payment for <strong class="text-slate-700 dark:text-slate-200">Invoice #{{ $invoice->invoice_number }}</strong> has been processed successfully.
            </p>
        </div>

        <!-- Receipt Box -->
        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700/60 text-left space-y-3 text-xs">
            <div class="flex justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
                <span class="text-slate-500">Amount Paid:</span>
                <span class="font-extrabold text-slate-900 dark:text-white text-sm font-mono">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Paid To:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $invoice->user->company_name ?: $invoice->user->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Billed To:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $invoice->client->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Payment Date:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $invoice->paid_at ? $invoice->paid_at->format('M d, Y h:i A') : now()->format('M d, Y') }}</span>
            </div>
            @if($invoice->stripe_payment_intent_id)
            <div class="flex justify-between pt-1 text-[11px]">
                <span class="text-slate-400">Reference:</span>
                <span class="font-mono text-slate-500 truncate max-w-[180px]">{{ $invoice->stripe_payment_intent_id }}</span>
            </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="space-y-3 pt-2">
            <a href="{{ route('invoices.public.pdf', $invoice->public_token) }}" 
               class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center justify-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i>
                Download Official PDF Receipt
            </a>

            <a href="{{ route('invoices.public', $invoice->public_token) }}" 
               class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition flex items-center justify-center gap-2">
                <i data-lucide="eye" class="w-4 h-4"></i>
                View Invoice Online
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
