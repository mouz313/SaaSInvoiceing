<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation #{{ $estimate->estimate_number }} &bull; {{ $estimate->user->company_name ?: $estimate->user->name }}</title>
    @include('partials.head-assets')
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-white min-h-screen py-8 px-4 sm:px-6">
    <div class="max-w-4xl mx-auto space-y-6" x-data="{ declineModalOpen: false }">
        <!-- Brand Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-3">
                @if($estimate->logo)
                    <img src="{{ $estimate->logo->url }}" alt="Logo" class="h-10 w-auto max-w-[140px] object-contain">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md shadow-indigo-500/20">
                        <i data-lucide="file-text" class="w-5 h-5"></i>
                    </div>
                @endif
                <div>
                    <h2 class="font-extrabold text-sm sm:text-base text-slate-900 dark:text-white leading-tight">
                        {{ $estimate->user->company_name ?: $estimate->user->name }}
                    </h2>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Commercial Proposal &bull; #{{ $estimate->estimate_number }}</span>
                </div>
            </div>

            <!-- Client Action Buttons -->
            <div class="flex items-center gap-2">
                @if($estimate->status !== 'accepted' && ! $estimate->isInvoiced())
                    <form action="{{ route('estimates.public.accept', $estimate->public_token) }}" method="POST" onsubmit="return confirm('Accept this quotation proposal?');" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            Accept Proposal
                        </button>
                    </form>

                    <button type="button" @click="declineModalOpen = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-rose-600 font-semibold text-xs hover:bg-rose-50 dark:hover:bg-rose-950/40 transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        Decline
                    </button>
                @endif

                <a href="{{ route('estimates.pdf', $estimate) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs hover:bg-slate-50 transition">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    PDF
                </a>
            </div>
        </div>

        <!-- Feedback Alert -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 rounded-2xl bg-slate-200 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold">
                {{ session('info') }}
            </div>
        @endif

        @if($estimate->isAccepted())
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-900 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-200 font-bold">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                    <span>Proposal Accepted! Our team will contact you shortly with next steps.</span>
                </div>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium">{{ $estimate->accepted_at?->format('M d, Y') }}</span>
            </div>
        @elseif($estimate->isDeclined())
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/50 border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-200 font-medium flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600"></i>
                <span>This proposal has been marked as declined.</span>
            </div>
        @endif

        <!-- Proposal Card -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
            @include('estimates.templates.' . $estimate->style, ['estimate' => $estimate, 'isPdf' => false])
        </div>

        <div class="text-center text-xs text-slate-400 py-4">
            Commercial Proposal issued via {{ setting('app_name', config('app.name', 'InvoiceHub')) }}
        </div>

        <!-- Decline Modal -->
        <div x-show="declineModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="declineModalOpen = false" class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="x-circle" class="w-5 h-5 text-rose-600"></i>
                    Decline Proposal
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Are you sure you want to decline this proposal? You may optionally provide feedback or reason:
                </p>

                <form action="{{ route('estimates.public.decline', $estimate->public_token) }}" method="POST" class="space-y-4">
                    @csrf
                    <textarea name="reason" rows="3" placeholder="Reason (optional, e.g. budget, timeline)..." 
                              class="w-full px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="declineModalOpen = false" class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition">
                            Confirm Decline
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
