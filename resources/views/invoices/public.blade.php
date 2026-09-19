<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $invoice->invoice_number }} — {{ $invoice->user->company_name ?: $invoice->user->name }}</title>

    @include('partials.head-assets')

    <style>
        @media print {
            header, footer, .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
            }
            #printable-invoice, #printable-invoice * {
                visibility: visible !important;
            }
            #printable-invoice {
                display: block !important;
                position: relative !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
            }
            .dark #printable-invoice {
                background: #ffffff !important;
                color: #0f172a !important;
            }
            .dark #printable-invoice * {
                color: #0f172a !important;
            }
            .dark #printable-invoice .text-slate-400,
            .dark #printable-invoice .text-slate-500 {
                color: #64748b !important;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-full flex flex-col antialiased">

    <!-- Top Client Action Header -->
    <header class="no-print sticky top-0 z-30 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                @if($invoice->logo)
                    <img src="{{ $invoice->logo->url }}" alt="Logo" class="h-8 max-w-[120px] object-contain">
                @endif
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-slate-900 dark:text-white text-base">Invoice #{{ $invoice->invoice_number }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                            @if($invoice->status === 'paid') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300
                            @elseif($invoice->status === 'sent') bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300
                            @elseif($invoice->status === 'overdue') bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300
                            @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 @endif">
                            {{ $invoice->status }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Issued by <strong class="text-slate-700 dark:text-slate-200">{{ $invoice->user->company_name ?: $invoice->user->name }}</strong>
                    </p>
                </div>
            </div>

            <!-- Client Buttons -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Download PDF -->
                <a href="{{ route('invoices.public.pdf', $invoice->public_token) }}" 
                   class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition inline-flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    Download PDF
                </a>

                <!-- Print -->
                <button type="button" onclick="window.print()" 
                        class="hidden sm:inline-flex px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition items-center gap-1 border border-slate-200 dark:border-slate-700">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                </button>

                <!-- Pay Button or Paid confirmation -->
                @if($invoice->isPaid())
                    <span class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm inline-flex items-center gap-1.5" style="background-color: #059669 !important; color: #ffffff !important;">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-white" style="color: #ffffff !important;"></i>
                        <span style="color: #ffffff !important;">Paid in Full</span>
                    </span>
                @else
                    <form method="POST" action="{{ route('invoices.public.checkout', $invoice->public_token) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                style="background-color: #059669 !important; color: #ffffff !important;"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/30 transition inline-flex items-center gap-2 cursor-pointer border-0">
                            <i data-lucide="credit-card" class="w-4 h-4 text-white" style="color: #ffffff !important;"></i>
                            <span class="text-white font-bold" style="color: #ffffff !important;">
                                Pay Balance: {{ $invoice->currency }} {{ number_format($invoice->balance_due ?? $invoice->total, 2) }}
                            </span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    <!-- Session Feedback -->
    @if(session('info'))
        <div class="no-print max-w-5xl mx-auto px-4 mt-4 w-full">
            <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4"></i>
                {{ session('info') }}
            </div>
        </div>
    @endif
    @if(session('success'))
        <div class="no-print max-w-5xl mx-auto px-4 mt-4 w-full">
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="no-print max-w-5xl mx-auto px-4 mt-4 w-full">
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Invoice Paper Container -->
    <main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 print:p-0">
        <div id="printable-invoice" class="max-w-4xl mx-auto bg-white dark:bg-slate-900 rounded-3xl shadow-xl shadow-slate-300/40 dark:shadow-black/50 border border-slate-200 dark:border-slate-800 overflow-hidden print:border-none print:shadow-none print:rounded-none">
            @php
                $viewName = match ($invoice->style) {
                    'corporate' => 'invoices.templates.corporate',
                    'creative' => 'invoices.templates.creative',
                    'grid' => 'invoices.templates.grid',
                    default => 'invoices.templates.minimalist',
                };
            @endphp

            @include($viewName, ['invoice' => $invoice, 'isPdf' => false])
        </div>

        <!-- Payment History Ledger (if payments exist) -->
        @if($invoice->payments->count() > 0)
        <div class="no-print max-w-4xl mx-auto mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i>
                    Payments Made to Date
                </h3>
                <div class="text-xs font-bold text-slate-900 dark:text-white">
                    Total Paid: <span class="text-emerald-600">{{ $invoice->currency }} {{ number_format($invoice->amount_paid ?? 0, 2) }}</span>
                    @if(($invoice->balance_due ?? 0) > 0)
                        &bull; Balance Remaining: <span class="text-rose-600">{{ $invoice->currency }} {{ number_format($invoice->balance_due, 2) }}</span>
                    @endif
                </div>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                @foreach($invoice->payments as $pay)
                <div class="py-2.5 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $pay->paid_at?->format('M d, Y') }}</span>
                        <span class="text-slate-400 text-[11px] ml-2 font-mono">({{ strtoupper(str_replace('_', ' ', $pay->payment_method)) }})</span>
                    </div>
                    <div class="font-extrabold text-emerald-600 dark:text-emerald-400">
                        + {{ $invoice->currency }} {{ number_format($pay->amount, 2) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Remittance / Payment Callout if unpaid -->
        @if(!$invoice->isPaid())
        <div class="no-print max-w-4xl mx-auto mt-6 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl p-6"
             x-data="{
                fullBalance: {{ (float)($invoice->balance_due ?? $invoice->total) }},
                payAmount: {{ (float)($invoice->balance_due ?? $invoice->total) }},
                isPartial: false
             }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-emerald-200/60 dark:border-emerald-800/40">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                        <i data-lucide="shield-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">Secure Online Card Checkout</h3>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400">Pay securely via debit or credit card. Instant digital receipt issued upon payment.</p>
                    </div>
                </div>

                <div class="text-right">
                    <div class="text-[11px] uppercase tracking-wider font-bold text-emerald-800 dark:text-emerald-300">Balance Due</div>
                    <div class="text-xl font-extrabold text-emerald-950 dark:text-white">
                        {{ $invoice->currency }} {{ number_format($invoice->balance_due ?? $invoice->total, 2) }}
                    </div>
                </div>
            </div>

            <!-- Payment Options -->
            <form method="POST" action="{{ route('invoices.public.checkout', $invoice->public_token) }}" class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                @csrf

                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-emerald-900 dark:text-emerald-200">
                        <input type="radio" name="payment_mode" @change="isPartial = false; payAmount = fullBalance;" checked class="text-emerald-600 focus:ring-emerald-500">
                        <span>Pay Full Balance ({{ $invoice->currency }} {{ number_format($invoice->balance_due ?? $invoice->total, 2) }})</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-emerald-900 dark:text-emerald-200">
                        <input type="radio" name="payment_mode" @change="isPartial = true;" class="text-emerald-600 focus:ring-emerald-500">
                        <span>Partial Payment</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <div x-show="isPartial" class="relative w-36" x-cloak>
                        <input type="number" name="amount" x-model.number="payAmount" step="0.01" min="1" :max="fullBalance"
                               class="w-full pl-8 pr-3 py-2 rounded-xl text-xs font-bold border border-emerald-300 dark:border-emerald-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">{{ $invoice->currency }}</span>
                    </div>

                    <button type="submit" 
                            style="background-color: #059669 !important; color: #ffffff !important;"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap border-0">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-white" style="color: #ffffff !important;"></i>
                        <span class="text-white font-bold" style="color: #ffffff !important;" x-text="'Checkout ' + '{{ $invoice->currency }} ' + (parseFloat(payAmount) || 0).toFixed(2)"></span>
                    </button>
                </div>
            </form>
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="no-print py-6 border-t border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
        <p>Secured by <span class="font-bold text-slate-600 dark:text-slate-300">{{ config('app.name', 'InvoiceHub') }}</span></p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
