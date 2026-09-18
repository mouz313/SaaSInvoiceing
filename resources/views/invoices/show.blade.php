@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12"
     x-data="{ 
        sendModalOpen: false,
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
                        @elseif($invoice->status === 'sent') bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400
                        @elseif($invoice->status === 'overdue') bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400
                        @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 @endif">
                        {{ strtoupper($invoice->status) }}
                    </span>

                    @if($invoice->viewed_at)
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-full" title="First viewed by client: {{ $invoice->viewed_at->format('M d, Y H:i') }}">
                            <i data-lucide="eye" class="w-3 h-3 text-blue-500"></i>
                            Viewed
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Billed to: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $invoice->client->name }}</span></p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Send Email to Client -->
            <button type="button" @click="sendModalOpen = true" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition cursor-pointer">
                <i data-lucide="send" class="w-4 h-4"></i>
                Send to Client
            </button>

            <!-- Copy Public Pay Link -->
            <button type="button" @click="copyLink()" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs transition border border-slate-200 dark:border-slate-700 cursor-pointer">
                <i data-lucide="link" class="w-4 h-4"></i>
                <span x-text="linkCopied ? 'Link Copied!' : 'Copy Pay Link'"></span>
            </button>

            <!-- Download PDF Button -->
            <a href="{{ route('invoices.pdf', $invoice) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
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
                @foreach(['minimalist' => 'Modern Minimalist', 'corporate' => 'Corporate Classic', 'creative' => 'Creative Bold', 'grid' => 'Clean Grid'] as $key => $label)
                <form method="POST" action="{{ route('invoices.update-style', $invoice) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="style" value="{{ $key }}">
                    <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $invoice->style === $key ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </button>
                </form>
                @endforeach
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
        @if($invoice->style === 'corporate')
            @include('invoices.templates.corporate', ['invoice' => $invoice, 'isPdf' => false])
        @elseif($invoice->style === 'creative')
            @include('invoices.templates.creative', ['invoice' => $invoice, 'isPdf' => false])
        @elseif($invoice->style === 'grid')
            @include('invoices.templates.grid', ['invoice' => $invoice, 'isPdf' => false])
        @else
            @include('invoices.templates.minimalist', ['invoice' => $invoice, 'isPdf' => false])
        @endif
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
