<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview: {{ $template->name }} — {{ config('app.name', 'InvoiceHub') }}</title>

    @include('partials.head-assets')

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            main { padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-full flex flex-col antialiased">
    <!-- Top Action Bar -->
    <header class="no-print sticky top-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 shadow-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ url()->previous() ?: route('templates.index') }}" 
                   class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition flex items-center gap-1">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Back</span>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-slate-900 dark:text-white text-sm sm:text-base">{{ $template->name }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $template->is_free ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300' }}">
                            {{ $template->is_free ? 'Free' : '$'.number_format($template->price, 2) }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-[10px] font-medium text-slate-600 dark:text-slate-400">
                            {{ $template->category }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500">Live Mockup Preview with Sample Financial Data</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold flex items-center gap-1.5 transition">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Print Test</span>
                </button>
                @if(!$isOwned && !$template->is_free)
                    <form method="POST" action="{{ route('templates.checkout', $template->slug) }}">
                        @csrf
                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                            <span>Unlock for ${{ number_format($template->price, 2) }}</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    <!-- Template Live Render Canvas -->
    <main class="flex-1 py-8 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            @php
                $templateView = view()->exists('invoices.templates.' . $template->slug) 
                    ? 'invoices.templates.' . $template->slug 
                    : 'invoices.templates.minimalist';
            @endphp

            @include($templateView, ['invoice' => $invoice, 'isPdf' => false])
        </div>
    </main>
</body>
</html>
