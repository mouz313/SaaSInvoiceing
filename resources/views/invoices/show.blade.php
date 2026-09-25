@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12"
     x-data="{ 
        sendModalOpen: false,
        paymentModalOpen: false,
        linkCopied: false,
        publicUrl: '{{ $invoice->public_url }}',
        copyLink() {
            navigator.clipboard.writeText(this.publicUrl).then(() => {
                this.linkCopied = true;
                setTimeout(() => { this.linkCopied = false; }, 2500);
            });
        }
     }">

    <!-- Top Action Toolbar -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="flex items-center gap-3">
            <a href="{{ route('invoices.index') }}" class="p-2 rounded-xl text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">#{{ $invoice->invoice_number }}</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                        @if($invoice->status === 'paid') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400
                        @elseif($invoice->status === 'partially_paid') bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400
                        @elseif($invoice->status === 'sent') bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400
                        @elseif($invoice->status === 'overdue') bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400
                        @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 @endif">
                        {{ strtoupper(str_replace('_', ' ', $invoice->status)) }}
                    </span>

                    @if($invoice->recurringInvoice)
                        <a href="{{ route('recurring.show', $invoice->recurringInvoice) }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/50 px-2 py-0.5 rounded-full">
                            <i data-lucide="repeat" class="w-3 h-3"></i>
                            Recurring Profile
                        </a>
                    @endif

                    @if($invoice->viewed_at)
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full" title="First viewed by client: {{ $invoice->viewed_at->format('M d, Y H:i') }}">
                            <i data-lucide="eye" class="w-3 h-3 text-blue-500"></i>
                            Viewed
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                    <span>Billed to: <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->client->name }}</strong></span>
                    <span>&bull;</span>
                    <span>Total: <strong class="text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</strong></span>
                    @if(($invoice->amount_paid ?? 0) > 0)
                        <span>&bull;</span>
                        <span class="text-emerald-600 font-bold">Paid: {{ $invoice->currency }} {{ number_format($invoice->amount_paid, 2) }}</span>
                        <span>&bull;</span>
                        <span class="text-rose-600 font-bold">Balance: {{ $invoice->currency }} {{ number_format($invoice->balance_due ?? 0, 2) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <!-- Record Payment Button -->
            @if(!$invoice->isPaid())
            <button type="button" @click="paymentModalOpen = true"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                <span>Record Payment</span>
            </button>
            @endif

            <!-- Send Email to Client -->
            <button type="button" @click="sendModalOpen = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition cursor-pointer">
                <i data-lucide="send" class="w-4 h-4"></i>
                Send to Client
            </button>

            <!-- Share via WhatsApp -->
            @php
                $waInvoiceText = urlencode("Hello {$invoice->client->name}, here is your invoice #{$invoice->invoice_number} for {$invoice->currency} " . number_format($invoice->total, 2) . " due on " . $invoice->due_date->format('M d, Y') . ". You can view and pay online here: " . $invoice->public_url);
                $waClientPhone = preg_replace('/[^0-9]/', '', $invoice->client->phone ?? '');
            @endphp
            <a href="https://wa.me/{{ $waClientPhone }}?text={{ $waInvoiceText }}" target="_blank" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer" title="Send directly via WhatsApp">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                <span>WhatsApp</span>
            </a>

            <!-- Copy Public Pay Link -->
            <button type="button" @click="copyLink()" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition border border-slate-200 dark:border-slate-700 cursor-pointer">
                <i data-lucide="link" class="w-4 h-4"></i>
                <span x-text="linkCopied ? 'Link Copied!' : 'Copy Pay Link'"></span>
            </button>

            <!-- Download PDF Button -->
            <a href="{{ route('invoices.pdf', $invoice) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition border border-slate-200 dark:border-slate-700">
                <i data-lucide="download" class="w-4 h-4"></i>
                PDF
            </a>

            <!-- Edit Button -->
            <a href="{{ route('invoices.edit', $invoice) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition border border-slate-200 dark:border-slate-700">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                Edit
            </a>
        </div>
    </div>

    <!-- Public Payment Link & Client Status Callout -->
    <div class="no-print bg-slate-50 dark:bg-slate-850 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <i data-lucide="globe" class="w-4 h-4"></i>
            </div>
            <div>
                <span class="font-bold text-slate-900 dark:text-white block">Public Client Portal &amp; Payment Link</span>
                <span class="text-slate-500 dark:text-slate-400 font-mono text-[11px] truncate max-w-sm sm:max-w-md block" x-text="publicUrl"></span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a :href="publicUrl" target="_blank" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-bold transition flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                View Client Portal
            </a>
            <button type="button" @click="copyLink()" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold transition flex items-center gap-1.5 shadow-xs">
                <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                <span x-text="linkCopied ? 'Copied!' : 'Copy'"></span>
            </button>
        </div>
    </div>

    <!-- Interactive Style Switcher & Status Bar -->
    <div class="no-print bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Style Switcher -->
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1">Style Template:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
                @php
                    $availableTemplates = \App\Models\InvoiceTemplate::where('is_active', true)->orderBy('sort_order')->get();
                    $ownedSlugs = Auth::user() ? Auth::user()->ownedTemplateSlugs() : ['minimalist', 'corporate', 'creative', 'grid'];
                @endphp
                @foreach($availableTemplates as $t)
                    @if(in_array($t->slug, $ownedSlugs, true) || $t->slug === $invoice->style)
                    <form method="POST" action="{{ route('invoices.update-style', $invoice) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="style" value="{{ $t->slug }}">
                        <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $invoice->style === $t->slug ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                            {{ $t->name }}
                        </button>
                    </form>
                    @endif
                @endforeach
                <a href="{{ route('templates.index') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition flex items-center gap-1">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> + More Themes
                </a>
            </div>
        </div>

        <!-- Status Switcher -->
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mr-1">Status:</span>
            <div class="flex items-center gap-1">
                @foreach(['draft' => 'Draft', 'sent' => 'Sent', 'paid' => 'Paid', 'overdue' => 'Overdue'] as $st => $stLabel)
                <form method="POST" action="{{ route('invoices.update-status', $invoice) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $st }}">
                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase tracking-wider transition {{ $invoice->status === $st ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}">
                        {{ $stLabel }}
                    </button>
                </form>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Paper Container with Selected Style -->
    <div id="printable-invoice" class="rounded-3xl bg-white dark:bg-slate-900 shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden min-h-[700px] transition-all">
        @php
            $viewName = view()->exists("invoices.templates.{$invoice->style}")
                ? "invoices.templates.{$invoice->style}"
                : 'invoices.templates.minimalist';
        @endphp
        @include($viewName, ['invoice' => $invoice, 'isPdf' => false])
    </div>

    <!-- Payment Records & Transaction History -->
    @if($invoice->payments->count() > 0 || ($invoice->amount_paid ?? 0) > 0)
    <div class="no-print p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Payment Receipts &amp; Ledger</h3>
                    <p class="text-xs text-slate-500">Record of all partial or full payments received for this invoice</p>
                </div>
            </div>

            <div class="text-right">
                <div class="text-xs text-slate-400">Total Paid / Balance Due</div>
                <div class="text-sm font-black">
                    <span class="text-emerald-600">{{ $invoice->currency }} {{ number_format($invoice->amount_paid ?? 0, 2) }}</span>
                    <span class="text-slate-400">/</span>
                    <span class="{{ ($invoice->balance_due ?? 0) > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $invoice->currency }} {{ number_format($invoice->balance_due ?? 0, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-2.5 px-3">Date</th>
                        <th class="py-2.5 px-3">Method</th>
                        <th class="py-2.5 px-3">Reference / Tx ID</th>
                        <th class="py-2.5 px-3">Notes</th>
                        <th class="py-2.5 px-3 text-right">Amount</th>
                        <th class="py-2.5 px-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($invoice->payments as $payment)
                    <tr>
                        <td class="py-2.5 px-3 font-medium text-slate-800 dark:text-slate-200">{{ $payment->paid_at?->format('M d, Y') }}</td>
                        <td class="py-2.5 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 uppercase">
                                {{ str_replace('_', ' ', $payment->payment_method) }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 font-mono text-[11px] text-slate-500">{{ $payment->reference_number ?: '—' }}</td>
                        <td class="py-2.5 px-3 text-slate-500 max-w-xs truncate">{{ $payment->notes ?: '—' }}</td>
                        <td class="py-2.5 px-3 text-right font-extrabold text-emerald-600 dark:text-emerald-400">+ {{ $invoice->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td class="py-2.5 px-3 text-right">
                            <form action="{{ route('invoices.payments.destroy', [$invoice, $payment]) }}" method="POST" class="inline" onsubmit="return confirm('Delete this payment record and revert balance?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-slate-400 hover:text-rose-500 rounded transition">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-3 text-center text-slate-400">No individual payment logs found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Record Payment Modal -->
    <div x-show="paymentModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-2xl space-y-5"
             @click.away="paymentModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Record Payment</h3>
                        <p class="text-xs text-slate-500">Log a manual or partial payment received</p>
                    </div>
                </div>
                <button type="button" @click="paymentModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.payments.store', $invoice) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="payment_amount" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        Amount to Record ({{ $invoice->currency }}) *
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0.01" max="{{ (float)($invoice->balance_due ?? $invoice->total) }}" name="amount" id="payment_amount" required 
                               value="{{ (float)($invoice->balance_due ?? $invoice->total) }}" 
                               class="w-full pl-8 pr-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-bold focus:ring-2 focus:ring-emerald-500">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">{{ $invoice->currency }}</span>
                    </div>
                    <span class="text-[11px] text-slate-500 mt-1 block">
                        Remaining balance: <strong>{{ $invoice->currency }} {{ number_format($invoice->balance_due ?? $invoice->total, 2) }}</strong>
                    </span>
                </div>

                <div>
                    <label for="payment_method" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Payment Method *</label>
                    <select name="payment_method" id="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-medium focus:ring-2 focus:ring-emerald-500">
                        <option value="bank_transfer">Bank Transfer / Wire</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="credit_card">Credit / Debit Card</option>
                        <option value="stripe">Stripe / Online</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="paid_at" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Date Received *</label>
                    <input type="date" name="paid_at" id="paid_at" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="reference_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Reference / Transaction # (Optional)</label>
                    <input type="text" name="reference_number" id="reference_number" placeholder="e.g. TR-92841, Cheque #4021" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Notes (Optional)</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="e.g. 50% advance received via Meezan Bank" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="paymentModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                        Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Send to Client Modal -->
    <div x-show="sendModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-cloak>
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-2xl space-y-6"
             @click.away="sendModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i data-lucide="mail" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Send Invoice by Email</h3>
                        <p class="text-xs text-slate-500">Client will receive invoice summary, payment link & PDF.</p>
                    </div>
                </div>
                <button type="button" @click="sendModalOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('invoices.send-email', $invoice) }}" class="space-y-4">
                @csrf

                <div>
                    <label for="recipient_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Recipient Email *</label>
                    <input type="email" name="recipient_email" id="recipient_email" required value="{{ old('recipient_email', $invoice->client->email) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="custom_message" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Custom Message (Optional)</label>
                    <textarea name="custom_message" id="custom_message" rows="3" placeholder="e.g. Hi {{ $invoice->client->name }}, please find attached invoice #{{ $invoice->invoice_number }} for our recent deliverables..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="attach_pdf" id="attach_pdf" value="1" checked class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                    <label for="attach_pdf" class="text-xs font-medium text-slate-700 dark:text-slate-300">Attach official PDF to this email</label>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="sendModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-2">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        Send Invoice Now
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<style>
@media print {
    body { background: white !important; color: black !important; padding: 0 !important; margin: 0 !important; }
    aside, header, .no-print { display: none !important; }
    main { padding: 0 !important; margin: 0 !important; }
    #printable-invoice { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; border-radius: 0 !important; }
}
</style>
@endpush
@endsection
