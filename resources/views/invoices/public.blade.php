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

                <!-- Client Portal Link -->
                @if($invoice->client)
                    <a href="{{ $invoice->client->portal_url }}" 
                       class="px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/60 dark:hover:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-bold text-xs transition inline-flex items-center gap-1.5 border border-blue-200 dark:border-blue-800"
                       title="View all your invoices & statement in your private client portal">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-blue-600"></i>
                        Client Portal
                    </a>
                @endif

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
                $viewName = view()->exists("invoices.templates.{$invoice->style}")
                    ? "invoices.templates.{$invoice->style}"
                    : 'invoices.templates.minimalist';
            @endphp

            @include($viewName, ['invoice' => $invoice, 'isPdf' => false])
        </div>

        @if($invoice->isFbrSynced())
        <div class="max-w-4xl mx-auto mt-4 no-print">
            @include('partials.fbr-badge', ['invoice' => $invoice])
        </div>
        @endif

        <!-- Payment History Ledger (if payments exist) -->
        @if($invoice->payments->count() > 0)
        <div class="no-print max-w-4xl mx-auto mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-emerald-600"></i>
                    Payments &amp; Remittance History
                </h3>
                <div class="text-xs font-bold text-slate-900 dark:text-white">
                    Total Settled: <span class="text-emerald-600">{{ $invoice->currency }} {{ number_format($invoice->amount_paid ?? 0, 2) }}</span>
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
                        @if($pay->reference_number)
                            <span class="text-slate-500 text-[11px] ml-1">Ref: <strong class="font-mono text-slate-700 dark:text-slate-300">{{ $pay->reference_number }}</strong></span>
                        @endif
                        @if($pay->status === 'pending_verification')
                            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                ⏳ Pending Verification
                            </span>
                        @elseif($pay->status === 'rejected')
                            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                Rejected
                            </span>
                        @else
                            <span class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                ✓ Verified
                            </span>
                        @endif
                    </div>
                    <div class="font-extrabold {{ $pay->status === 'completed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500' }}">
                        + {{ $invoice->currency }} {{ number_format($pay->amount, 2) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Pending Proof Notice (if any) -->
        @if($invoice->payments->where('status', 'pending_verification')->isNotEmpty())
        <div class="no-print max-w-4xl mx-auto mt-4 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 text-amber-900 dark:text-amber-200 text-xs flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <i data-lucide="clock" class="w-5 h-5 text-amber-600 shrink-0"></i>
                <div>
                    <span class="font-bold">Transaction Reference Submitted:</span>
                    <span>Your payment proof is currently under review by {{ $invoice->user->company_name ?: $invoice->user->name }}. You will receive a receipt once verified.</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Remittance / Payment Callout if unpaid -->
        @if(!$invoice->isPaid())
        <div class="no-print max-w-4xl mx-auto mt-6 space-y-4"
             x-data="{
                fullBalance: {{ (float)($invoice->balance_due ?? $invoice->total) }},
                payAmount: {{ (float)($invoice->balance_due ?? $invoice->total) }},
                isPartial: false,
                proofModalOpen: false,
                copiedKey: '',
                copyText(text, key) {
                    navigator.clipboard.writeText(text).then(() => {
                        this.copiedKey = key;
                        setTimeout(() => { this.copiedKey = ''; }, 2000);
                    });
                }
             }">

            <!-- Promotional Coupon / Discount Engine -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xs"
                 x-data="{
                    couponCode: '{{ $invoice->coupon_code }}',
                    hasCoupon: {{ $invoice->coupon_code ? 'true' : 'false' }},
                    loading: false,
                    message: '',
                    isError: false,
                    applyCoupon() {
                        if (!this.couponCode.trim()) return;
                        this.loading = true;
                        this.message = '';
                        this.isError = false;

                        fetch('{{ route('invoices.public.coupon.apply', $invoice->public_token) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ code: this.couponCode })
                        })
                        .then(res => res.json().then(data => ({ status: res.status, body: data })))
                        .then(({ status, body }) => {
                            this.loading = false;
                            if (status === 200 && body.success) {
                                this.hasCoupon = true;
                                this.message = body.message;
                                this.isError = false;
                                setTimeout(() => window.location.reload(), 600);
                            } else {
                                this.isError = true;
                                this.message = body.message || 'Failed to apply coupon.';
                            }
                        })
                        .catch(err => {
                            this.loading = false;
                            this.isError = true;
                            this.message = 'Network error. Please try again.';
                        });
                    },
                    removeCoupon() {
                        this.loading = true;
                        fetch('{{ route('invoices.public.coupon.remove', $invoice->public_token) }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(() => window.location.reload())
                        .catch(() => window.location.reload());
                    }
                 }">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <i data-lucide="ticket-percent" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white">Have a Promo or Coupon Code?</h4>
                            <p class="text-[11px] text-slate-400">Apply discounts provided by {{ $invoice->user->company_name ?: $invoice->user->name }}.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="!hasCoupon">
                            <div class="flex items-center gap-1.5 w-full sm:w-auto">
                                <input type="text" x-model="couponCode" @keydown.enter.prevent="applyCoupon()" placeholder="ENTER CODE" class="uppercase font-mono text-xs px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 w-36 sm:w-44">
                                <button type="button" @click="applyCoupon()" :disabled="loading" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span x-text="loading ? 'Applying...' : 'Apply'"></span>
                                </button>
                            </div>
                        </template>

                        <template x-if="hasCoupon">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1 text-xs font-mono font-bold px-3 py-1.5 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                    Applied: {{ $invoice->coupon_code }} (-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }})
                                </span>
                                <button type="button" @click="removeCoupon()" class="text-xs text-rose-500 hover:text-rose-700 font-semibold underline p-1">Remove</button>
                            </div>
                        </template>
                    </div>
                </div>

                <div x-show="message" x-cloak class="mt-2 text-xs font-medium" :class="isError ? 'text-rose-500' : 'text-emerald-600'" x-text="message"></div>
            </div>

            <!-- Option 1: Card Online Checkout (Stripe) -->
            <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/80 rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-emerald-200/60 dark:border-emerald-800/40">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-emerald-900 dark:text-emerald-200">Online Card Checkout</h3>
                            <p class="text-xs text-emerald-700 dark:text-emerald-400">Pay securely via debit or credit card. Instant digital settlement.</p>
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
                            <span class="text-white font-bold" style="color: #ffffff !important;" x-text="'Card Checkout ' + '{{ $invoice->currency }} ' + (parseFloat(payAmount) || 0).toFixed(2)"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Option 2: Local Pakistani Payment Methods (Bank Transfer, Raast, JazzCash, EasyPaisa) -->
            @if($invoice->user->hasLocalPaymentDetails())
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center shrink-0">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                Direct Bank Transfer, Raast &amp; Mobile Wallets
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">0% Gateway Fee</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Transfer funds directly from your local Pakistani banking app, JazzCash, or EasyPaisa.</p>
                        </div>
                    </div>

                    <button type="button" @click="proofModalOpen = !proofModalOpen" 
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer self-start sm:self-auto">
                        <i data-lucide="file-check-2" class="w-4 h-4"></i>
                        <span x-text="proofModalOpen ? 'Hide Submission Form' : 'I Have Paid / Submit Proof'"></span>
                    </button>
                </div>

                <!-- Account Credentials Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Bank Account & Raast Card -->
                    @if($invoice->user->bank_account_number || $invoice->user->bank_iban || $invoice->user->raast_id)
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-2 text-xs">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
                            <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <i data-lucide="landmark" class="w-4 h-4 text-blue-600"></i>
                                {{ $invoice->user->bank_name ?: 'Bank Transfer (IBFT)' }}
                            </span>
                            @if($invoice->user->bank_account_title)
                                <span class="text-[11px] text-slate-500 font-medium truncate max-w-[150px]">{{ $invoice->user->bank_account_title }}</span>
                            @endif
                        </div>

                        @if($invoice->user->bank_account_number)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Account No:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $invoice->user->bank_account_number }}</span>
                                <button type="button" @click="copyText('{{ $invoice->user->bank_account_number }}', 'acc_num')" class="text-blue-600 hover:text-blue-700 p-1 cursor-pointer">
                                    <span x-show="copiedKey === 'acc_num'" class="text-[10px] text-emerald-600 font-bold">Copied!</span>
                                    <i x-show="copiedKey !== 'acc_num'" data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                        @endif

                        @if($invoice->user->bank_iban)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">IBAN:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-bold text-slate-900 dark:text-white text-[11px]">{{ $invoice->user->bank_iban }}</span>
                                <button type="button" @click="copyText('{{ $invoice->user->bank_iban }}', 'iban')" class="text-blue-600 hover:text-blue-700 p-1 cursor-pointer">
                                    <span x-show="copiedKey === 'iban'" class="text-[10px] text-emerald-600 font-bold">Copied!</span>
                                    <i x-show="copiedKey !== 'iban'" data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                        @endif

                        @if($invoice->user->raast_id)
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 dark:border-slate-700/60">
                            <span class="text-slate-500 font-bold text-emerald-700 dark:text-emerald-400">Raast ID:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">{{ $invoice->user->raast_id }}</span>
                                <button type="button" @click="copyText('{{ $invoice->user->raast_id }}', 'raast')" class="text-emerald-600 hover:text-emerald-700 p-1 cursor-pointer">
                                    <span x-show="copiedKey === 'raast'" class="text-[10px] text-emerald-600 font-bold">Copied!</span>
                                    <i x-show="copiedKey !== 'raast'" data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Mobile Wallets Card -->
                    @if($invoice->user->jazzcash_number || $invoice->user->easypaisa_number)
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
                            <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                <i data-lucide="smartphone" class="w-4 h-4 text-orange-600"></i>
                                Mobile Wallets (PK)
                            </span>
                            <span class="text-[10px] text-slate-400">Instant Push / Transfer</span>
                        </div>

                        @if($invoice->user->jazzcash_number)
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-bold text-red-600 dark:text-red-400">JazzCash</span>
                                @if($invoice->user->jazzcash_title)<span class="text-slate-400 text-[10px] block">({{ $invoice->user->jazzcash_title }})</span>@endif
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $invoice->user->jazzcash_number }}</span>
                                <button type="button" @click="copyText('{{ $invoice->user->jazzcash_number }}', 'jc')" class="text-red-600 hover:text-red-700 p-1 cursor-pointer">
                                    <span x-show="copiedKey === 'jc'" class="text-[10px] text-emerald-600 font-bold">Copied!</span>
                                    <i x-show="copiedKey !== 'jc'" data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                        @endif

                        @if($invoice->user->easypaisa_number)
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 dark:border-slate-700/60">
                            <div>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">EasyPaisa</span>
                                @if($invoice->user->easypaisa_title)<span class="text-slate-400 text-[10px] block">({{ $invoice->user->easypaisa_title }})</span>@endif
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $invoice->user->easypaisa_number }}</span>
                                <button type="button" @click="copyText('{{ $invoice->user->easypaisa_number }}', 'ep')" class="text-emerald-600 hover:text-emerald-700 p-1 cursor-pointer">
                                    <span x-show="copiedKey === 'ep'" class="text-[10px] text-emerald-600 font-bold">Copied!</span>
                                    <i x-show="copiedKey !== 'ep'" data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Proof Submission Form Drawer -->
                <div x-show="proofModalOpen" x-cloak class="pt-4 border-t border-slate-200 dark:border-slate-800">
                    <form method="POST" action="{{ route('invoices.public.proof', $invoice->public_token) }}" enctype="multipart/form-data" class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-4">
                        @csrf
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <i data-lucide="check-square" class="w-4 h-4 text-emerald-600"></i>
                                Submit Bank / Wallet Payment Reference
                            </h4>
                            <span class="text-[11px] text-slate-400">Once submitted, merchant will verify against bank statement.</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <label class="block text-slate-600 dark:text-slate-400 font-bold mb-1">Transfer Method *</label>
                                <select name="payment_method" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                    <option value="bank_transfer">Bank Transfer (IBFT)</option>
                                    <option value="raast">Raast Instant Transfer</option>
                                    <option value="jazzcash">JazzCash</option>
                                    <option value="easypaisa">EasyPaisa</option>
                                    <option value="other">Other Manual Settlement</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-600 dark:text-slate-400 font-bold mb-1">Transaction Ref / TRX ID *</label>
                                <input type="text" name="reference_number" required placeholder="e.g. TID-98231456" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold">
                            </div>

                            <div>
                                <label class="block text-slate-600 dark:text-slate-400 font-bold mb-1">Amount Paid ({{ $invoice->currency }}) *</label>
                                <input type="number" step="0.01" min="1" name="amount" value="{{ (float)($invoice->balance_due ?? $invoice->total) }}" required class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-bold">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-slate-600 dark:text-slate-400 font-bold mb-1">Payment Receipt / Screenshot (Optional)</label>
                                <input type="file" name="proof_file" accept="image/*,.pdf" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 dark:file:bg-blue-950/60 dark:file:text-blue-300">
                            </div>

                            <div>
                                <label class="block text-slate-600 dark:text-slate-400 font-bold mb-1">Additional Notes</label>
                                <input type="text" name="notes" placeholder="Sender account title or branch" class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>
                        </div>

                        <div class="pt-2 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                Submit Proof for Verification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
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
