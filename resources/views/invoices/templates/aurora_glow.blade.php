@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; }
        body { color: #1e293b; background: #ffffff; font-size: 12px; line-height: 1.4; padding: 25px 30px; }
        .banner { background: #4f46e5; color: #ffffff; padding: 22px 24px; border-radius: 8px; margin-bottom: 22px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 9999px; font-size: 9px; font-weight: 800; text-transform: uppercase; background: #ffffff; color: #4f46e5; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 8px 6px; text-align: left; font-size: 9px; font-weight: 800; text-transform: uppercase; color: #64748b; }
        .table td { padding: 9px 6px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
    </style>
</head>
<body>
    <div class="banner">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: middle; width: 60%;">
                    <div style="font-size: 24px; font-weight: 900; letter-spacing: -0.5px;">{{ strtoupper($invoice->user->company_name ?? $invoice->user->name) }}</div>
                    <div style="font-size: 10px; opacity: 0.85; margin-top: 3px;">Aurora Borealis SaaS Bill</div>
                </td>
                <td style="vertical-align: middle; width: 40%; text-align: right;">
                    <span class="badge">{{ strtoupper($invoice->status) }}</span>
                    <div style="font-size: 16px; font-weight: 800; margin-top: 4px;">#{{ $invoice->invoice_number }}</div>
                    <div style="font-size: 10px; opacity: 0.85;">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Client Card -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 18px;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">Billed Recipient</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 3px;">{{ $invoice->client->name }}</div>
                @if($invoice->client->company_name)<div style="font-size: 11px; color: #475569;">{{ $invoice->client->company_name }}</div>@endif
                @if($invoice->client->address)<div style="font-size: 10px; color: #64748b;">{{ $invoice->client->address }}</div>@endif
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">Payment Term</div>
                <div style="font-size: 11px; color: #475569; margin-top: 3px;">Due Date: <strong>{{ $invoice->due_date->format('M d, Y') }}</strong></div>
                <div style="font-size: 10px; color: #64748b;">Issuer: {{ $invoice->user->email }}</div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="table">
        <thead>
            <tr>
                <th style="width: 55%;">Creative Deliverable & Description</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Rate</th>
                <th style="width: 15%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="font-weight: 600; color: #0f172a;">{{ $item->description }}</td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right; font-weight: 700; color: #4f46e5;">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 25px;">
        <tr>
            <td style="width: 50%; vertical-align: middle;">
                <div style="font-size: 10px; font-weight: 700; color: #4f46e5;">STUDIO VERIFIED DIGITAL ASSET</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Deliverables delivered in accordance with client contract.</div>
            </td>
            <td style="width: 50%; vertical-align: top; text-align: right;">
                <div style="font-size: 11px; color: #64748b;">Subtotal: {{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</div>
                @if($invoice->tax_amount > 0)
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Tax: +{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</div>
                @endif
                <div style="font-size: 16px; font-weight: 900; color: #4f46e5; margin-top: 6px;">Total: {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
@else
{{-- Web View: Creative Ribbon & Floating Cards --}}
<div class="bg-white dark:bg-slate-900 rounded-3xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-800">
    <!-- Top Vibrant Gradient Ribbon Banner -->
    <div class="relative bg-gradient-to-r from-emerald-500 via-teal-600 to-indigo-600 p-8 sm:p-10 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-xs text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                    <i data-lucide="sparkles" class="w-3 h-3"></i> Creative Invoice
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $invoice->user->company_name ?? $invoice->user->name }}</h1>
                <p class="text-xs text-white/80 mt-1">Aurora Borealis SaaS Bill</p>
            </div>

            <div class="sm:text-right flex flex-col sm:items-end">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-white text-slate-900 shadow-md">
                    {{ $invoice->status }}
                </span>
                <span class="font-mono text-base font-bold text-white mt-2 block">#{{ $invoice->invoice_number }}</span>
                <span class="text-xs text-white/80 mt-0.5">{{ $invoice->invoice_date->format('M d, Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Floating Logo Card Overlapping the Banner -->
    <div class="px-8 -mt-6 relative z-20 flex flex-wrap items-center justify-between gap-4">
        <div class="invoice-logo-frame h-16 w-52 bg-white dark:bg-slate-800 rounded-2xl shadow-lg border border-slate-200 dark:border-slate-700 p-2 flex items-center justify-center">
            @if($invoice->logo)
                <img src="{{ $invoice->logo->url }}" alt="Company Logo" class="max-h-12 max-w-full object-contain">
            @elseif(!empty($invoice->user->logo_url))
                <img src="{{ $invoice->user->logo_url }}" alt="Company Logo" class="max-h-12 max-w-full object-contain">
            @else
                <div class="flex items-center gap-2 text-indigo-600 font-black text-sm">
                    <i data-lucide="palette" class="w-5 h-5"></i>
                    <span>{{ strtoupper(substr($invoice->user->company_name ?? $invoice->user->name, 0, 14)) }}</span>
                </div>
            @endif
        </div>

        <div class="inline-flex flex-col items-center select-none">
    <svg class="h-8 w-40 text-slate-800 dark:text-slate-300" viewBox="0 0 160 30" fill="currentColor">
        <rect x="0" y="0" width="3" height="28"/><rect x="5" y="0" width="2" height="28"/><rect x="9" y="0" width="4" height="28"/><rect x="16" y="0" width="2" height="28"/><rect x="21" y="0" width="3" height="28"/><rect x="27" y="0" width="6" height="28"/><rect x="36" y="0" width="2" height="28"/><rect x="41" y="0" width="4" height="28"/><rect x="48" y="0" width="3" height="28"/><rect x="54" y="0" width="5" height="28"/><rect x="62" y="0" width="2" height="28"/><rect x="67" y="0" width="4" height="28"/><rect x="74" y="0" width="3" height="28"/><rect x="80" y="0" width="6" height="28"/><rect x="89" y="0" width="2" height="28"/><rect x="94" y="0" width="5" height="28"/><rect x="102" y="0" width="3" height="28"/><rect x="108" y="0" width="4" height="28"/><rect x="115" y="0" width="2" height="28"/><rect x="120" y="0" width="6" height="28"/><rect x="129" y="0" width="3" height="28"/><rect x="135" y="0" width="4" height="28"/><rect x="142" y="0" width="2" height="28"/><rect x="147" y="0" width="5" height="28"/><rect x="155" y="0" width="3" height="28"/>
    </svg>
    <span class="font-mono text-[9px] text-slate-400 tracking-widest mt-0.5">AUTH-VERIFIED</span>
</div>
    </div>

    <!-- Body Container -->
    <div class="p-8 sm:p-10 space-y-8">
        <!-- Client & Meta Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Recipient Client</span>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $invoice->client->name }}</h3>
                @if($invoice->client->company_name)<p class="text-xs text-slate-600 dark:text-slate-300 font-medium">{{ $invoice->client->company_name }}</p>@endif
                @if($invoice->client->email)<p class="text-xs text-slate-500">{{ $invoice->client->email }}</p>@endif
            </div>

            <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700/80 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Payment Due</span>
                    <p class="text-sm font-bold text-slate-900 dark:text-white mt-1">{{ $invoice->due_date->format('M d, Y') }}</p>
                    <p class="text-xs text-slate-500">{{ $invoice->payment_instructions ?? 'Direct wire or card payment.' }}</p>
                </div>
                <div class="inline-flex flex-col items-center p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xs">
    <svg class="w-14 h-14" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="45" height="45" fill="white"/>
        <rect x="2" y="2" width="13" height="13" stroke="#0f172a" stroke-width="3" fill="none"/>
        <rect x="5" y="5" width="7" height="7" fill="#0f172a"/>
        <rect x="30" y="2" width="13" height="13" stroke="#0f172a" stroke-width="3" fill="none"/>
        <rect x="33" y="5" width="7" height="7" fill="#0f172a"/>
        <rect x="2" y="30" width="13" height="13" stroke="#0f172a" stroke-width="3" fill="none"/>
        <rect x="5" y="33" width="7" height="7" fill="#0f172a"/>
        <rect x="18" y="4" width="3" height="3" fill="#0f172a"/>
        <rect x="24" y="4" width="3" height="3" fill="#0f172a"/>
        <rect x="18" y="10" width="3" height="3" fill="#0f172a"/>
        <rect x="24" y="10" width="3" height="3" fill="#0f172a"/>
        <rect x="18" y="18" width="9" height="9" fill="#0f172a"/>
        <rect x="31" y="20" width="3" height="3" fill="#0f172a"/>
        <rect x="37" y="20" width="3" height="3" fill="#0f172a"/>
        <rect x="18" y="31" width="3" height="3" fill="#0f172a"/>
        <rect x="24" y="37" width="3" height="3" fill="#0f172a"/>
        <rect x="31" y="31" width="8" height="8" fill="#0f172a"/>
    </svg>
    <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest mt-1">Scan to Verify</span>
</div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-indigo-50/60 dark:bg-indigo-950/30 text-indigo-900 dark:text-indigo-300 font-bold uppercase tracking-wider rounded-xl">
                    <tr>
                        <th class="py-3 px-4 rounded-l-xl">Deliverable Description</th>
                        <th class="py-3 px-3 text-center">Qty</th>
                        <th class="py-3 px-4 text-right">Rate</th>
                        <th class="py-3 px-4 rounded-r-xl text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($invoice->items as $item)
                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                        <td class="py-4 px-4 font-semibold text-slate-900 dark:text-white">{{ $item->description }}</td>
                        <td class="py-4 px-3 text-center">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300">{{ $item->quantity }}</span>
                        </td>
                        <td class="py-4 px-4 text-right text-slate-600 dark:text-slate-300">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-4 px-4 text-right font-bold text-indigo-600 dark:text-indigo-400">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer & Signatures -->
        <div class="pt-6 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="inline-block transform -rotate-12 select-none opacity-85 hover:opacity-100 transition-opacity">
    <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="50" r="46" stroke="#6366f1" stroke-width="2" stroke-dasharray="3 2" />
        <circle cx="50" cy="50" r="40" stroke="#6366f1" stroke-width="1.5" />
        <circle cx="50" cy="50" r="16" fill="#6366f1" fill-opacity="0.08" stroke="#6366f1" stroke-width="1" />
        <text x="50" y="44" font-size="6.5" font-weight="900" text-anchor="middle" fill="#6366f1" letter-spacing="1">CREATIVE VERIFIED</text>
        <text x="50" y="54" font-size="8" font-weight="900" text-anchor="middle" fill="#6366f1" letter-spacing="0.5">VERIFIED</text>
        <text x="50" y="63" font-size="5.5" font-weight="800" text-anchor="middle" fill="#6366f1">★ AUTHENTIC ★</text>
    </svg>
</div>
                <div class="inline-block text-center">
    <svg class="w-36 h-10 text-slate-800 dark:text-slate-200 mx-auto" viewBox="0 0 150 40" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M10 28 C 25 15, 30 35, 45 18 C 55 5, 58 30, 70 20 C 85 10, 80 32, 95 18 C 110 5, 120 28, 140 22" />
        <path d="M35 32 C 60 30, 90 32, 130 30" stroke-width="1"/>
    </svg>
    <div class="w-40 border-t border-slate-300 dark:border-slate-700 pt-1">
        <p class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Authorized Signature</p>
        <p class="text-[9px] text-slate-400 font-medium">{{ $invoice->user->company_name ?? $invoice->user->name }}</p>
    </div>
</div>
            </div>

            <div class="w-full sm:w-80 rounded-2xl bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-slate-800 dark:to-slate-850 p-5 border border-indigo-100 dark:border-slate-700 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600 dark:text-slate-300">
                    <span>Subtotal:</span>
                    <span class="font-bold">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Discount:</span>
                    <span>-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-slate-600 dark:text-slate-300">
                    <span>Tax:</span>
                    <span>+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="pt-2 border-t border-indigo-200 dark:border-slate-700 flex justify-between items-baseline text-sm">
                    <span class="font-black text-slate-900 dark:text-white uppercase tracking-wider">Total Due:</span>
                    <span class="text-xl font-black text-indigo-600 dark:text-indigo-400">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif