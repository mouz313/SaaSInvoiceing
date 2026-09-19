@extends('layouts.app')

@section('title', 'Quotation #' . $estimate->estimate_number)

@section('content')
<div class="space-y-6 max-w-5xl mx-auto" x-data="{ emailModalOpen: false }">
    <!-- Top Bar: Back & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('estimates.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Back to Quotations
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">Quote #{{ $estimate->estimate_number }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-{{ $estimate->status_color }}-100 dark:bg-{{ $estimate->status_color }}-950/60 text-{{ $estimate->status_color }}-700 dark:text-{{ $estimate->status_color }}-400">
                    {{ $estimate->status }}
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            {{-- 1-Click Convert to Invoice --}}
            @if(! $estimate->isInvoiced())
                <form action="{{ route('estimates.convert', $estimate) }}" method="POST" onsubmit="return confirm('Convert this estimate into an official Invoice? (Consumes 1 invoice credit)');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-sm transition">
                        <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                        Convert to Invoice
                    </button>
                </form>
            @else
                <a href="{{ route('invoices.show', $estimate->converted_invoice_id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 font-bold text-xs transition">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                    View Invoice #{{ $estimate->convertedInvoice->invoice_number }}
                </a>
            @endif

            {{-- WhatsApp Share --}}
            @php
                $waText = urlencode("Hello {$estimate->client->name}, please review your quotation proposal #{$estimate->estimate_number} for {$estimate->currency} " . number_format($estimate->total, 2) . ". View and accept online here: " . $estimate->public_url);
                $waPhone = preg_replace('/[^0-9]/', '', $estimate->client->phone ?? '');
            @endphp
            <a href="https://wa.me/{{ $waPhone }}?text={{ $waText }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                WhatsApp
            </a>

            {{-- Send Email --}}
            <button type="button" @click="emailModalOpen = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition">
                <i data-lucide="mail" class="w-4 h-4"></i>
                Email Proposal
            </button>

            {{-- Download PDF --}}
            <a href="{{ route('estimates.pdf', $estimate) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-50 transition">
                <i data-lucide="download" class="w-4 h-4"></i>
                PDF
            </a>

            {{-- Public Link Copy --}}
            <button type="button" onclick="navigator.clipboard.writeText('{{ $estimate->public_url }}'); alert('Public proposal link copied to clipboard!');" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-50 transition" title="Copy Public Review Link">
                <i data-lucide="link" class="w-4 h-4"></i>
                Copy Link
            </button>

            {{-- Edit --}}
            @if(! $estimate->isInvoiced())
                <a href="{{ route('estimates.edit', $estimate) }}" class="p-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition" title="Edit Estimate">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </a>
            @endif

            {{-- Delete --}}
            <form action="{{ route('estimates.destroy', $estimate) }}" method="POST" onsubmit="return confirm('Delete this quotation?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition" title="Delete Estimate">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Status & Tracking Info Banner -->
    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs flex flex-wrap items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-6 text-slate-500 dark:text-slate-400">
            <span><strong>Client:</strong> {{ $estimate->client->name }}</span>
            <span><strong>Valid Until:</strong> {{ $estimate->expiry_date->format('M d, Y') }}</span>
            @if($estimate->viewed_at)
                <span class="text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> Viewed by client on {{ $estimate->viewed_at->format('M d, h:i A') }}
                </span>
            @else
                <span class="text-slate-400">Unviewed by client</span>
            @endif
            @if($estimate->accepted_at)
                <span class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Accepted on {{ $estimate->accepted_at->format('M d, Y') }}
                </span>
            @endif
            @if($estimate->declined_at)
                <span class="text-rose-600 dark:text-rose-400 font-bold flex items-center gap-1">
                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Declined: {{ $estimate->decline_reason ?: 'No reason given' }}
                </span>
            @endif
        </div>

        {{-- Quick Status Changer --}}
        <form action="{{ route('estimates.status', $estimate) }}" method="POST" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <label for="status_select" class="font-bold text-slate-700 dark:text-slate-300">Status:</label>
            <select name="status" id="status_select" onchange="this.form.submit()" 
                    class="px-2.5 py-1 rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-semibold text-xs focus:outline-none">
                <option value="draft" {{ $estimate->status === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="sent" {{ $estimate->status === 'sent' ? 'selected' : '' }}>Sent</option>
                <option value="accepted" {{ $estimate->status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                <option value="declined" {{ $estimate->status === 'declined' ? 'selected' : '' }}>Declined</option>
                <option value="invoiced" {{ $estimate->status === 'invoiced' ? 'selected' : '' }}>Invoiced</option>
            </select>
        </form>
    </div>

    @if($estimate->isInvoiced())
        <div class="p-4 rounded-2xl bg-purple-50 dark:bg-purple-950/50 border border-purple-200 dark:border-purple-900 flex items-center justify-between text-purple-900 dark:text-purple-200 text-xs sm:text-sm">
            <div class="flex items-center gap-2.5 font-bold">
                <i data-lucide="sparkles" class="w-5 h-5 text-purple-600"></i>
                <span>This proposal has been converted to Invoice #{{ $estimate->convertedInvoice?->invoice_number }}!</span>
            </div>
            <a href="{{ route('invoices.show', $estimate->converted_invoice_id) }}" class="underline font-bold hover:text-purple-700">
                View Generated Invoice &rarr;
            </a>
        </div>
    @endif

    <!-- Proposal Document Card -->
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        @include('estimates.templates.' . $estimate->style, ['estimate' => $estimate, 'isPdf' => false])
    </div>

    <!-- Email Modal -->
    <div x-show="emailModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="emailModalOpen = false" class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="mail" class="w-5 h-5 text-indigo-600"></i>
                    Email Quotation Proposal
                </h3>
                <button type="button" @click="emailModalOpen = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('estimates.send-email', $estimate) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="recipient_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Recipient Email *</label>
                    <input type="email" name="recipient_email" id="recipient_email" required value="{{ old('recipient_email', $estimate->client->email) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                </div>

                <div>
                    <label for="custom_message" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">Personal Message (Optional)</label>
                    <textarea name="custom_message" id="custom_message" rows="3" placeholder="Please review our quote for the project discussed..."
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-indigo-600 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="attach_pdf" id="attach_pdf" value="1" checked
                           class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 dark:border-slate-600">
                    <label for="attach_pdf" class="text-xs text-slate-700 dark:text-slate-300">Attach official PDF document to email</label>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" @click="emailModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1.5">
                        <i data-lucide="send" class="w-3.5 h-3.5"></i>
                        Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
