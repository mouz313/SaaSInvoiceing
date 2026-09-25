@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { color: #0f172a; background: #ffffff; font-size: 12px; line-height: 1.4; padding: 30px; }
        .header { border-bottom: 3px solid #0d9488; padding-bottom: 15px; margin-bottom: 20px; }
        .badge { display: inline-block; padding: 3px 8px; font-size: 9px; font-weight: 800; text-transform: uppercase; background: #0d9488; color: #ffffff; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { border-bottom: 2px solid #0f172a; padding: 8px 5px; text-align: left; font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }
        .table td { border-bottom: 1px solid #e2e8f0; padding: 8px 5px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: top; width: 60%;">
                    <!-- Dedicated Logo Frame -->
                    <div style="height: 55px; max-height: 55px; width: 180px; overflow: hidden; margin-bottom: 6px;">
                        @if($invoice->logo)
                            <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 50px; max-width: 175px; object-fit: contain; display: block;">
                        @else
                            <div style="font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">{{ strtoupper($invoice->user->company_name ?? $invoice->user->name) }}</div>
                        @endif
                    </div>
                    <div style="font-size: 11px; color: #64748b;">{{ $invoice->user->email }}</div>
                </td>
                <td style="vertical-align: top; width: 40%; text-align: right;">
                    <div style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -1px;">INVOICE</div>
                    <div style="font-size: 10px; color: #0d9488; font-weight: 700; text-transform: uppercase;">Clinical & Healthcare Provider Statement</div>
                    <span class="badge" style="margin-top: 4px;">{{ strtoupper($invoice->status) }}</span>
                    <div style="font-size: 13px; font-weight: 800; margin-top: 4px;">#{{ $invoice->invoice_number }}</div>
                    <div style="font-size: 10px; color: #64748b;">{{ $invoice->invoice_date->format('M d, Y') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Client Box -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">Client Profile</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $invoice->client->name }}</div>
                @if($invoice->client->company_name)<div style="font-size: 11px; color: #475569;">{{ $invoice->client->company_name }}</div>@endif
                @if($invoice->client->address)<div style="font-size: 10px; color: #64748b;">{{ $invoice->client->address }}</div>@endif
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">Due Terms</div>
                <div style="font-size: 12px; font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $invoice->due_date->format('M d, Y') }}</div>
                <div style="font-size: 10px; color: #64748b;">{{ $invoice->payment_instructions ?? 'Direct settlement.' }}</div>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 55%;">Item Particulars</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Rate</th>
                <th style="width: 15%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="font-weight: 600; color: #0f172a;">{{ $item->description }}</td>
                <td style="text-align: center; color: #475569;">{{ $item->quantity }}</td>
                <td style="text-align: right; color: #475569;">{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right; font-weight: 700; color: #0f172a;">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8;">VERIFIED INVOICE</div>
                <div style="font-size: 10px; color: #475569; margin-top: 2px;">Original document issued by authorized merchant.</div>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div style="font-size: 11px; color: #64748b;">Subtotal: {{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</div>
                @if($invoice->tax_amount > 0)
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Tax: +{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</div>
                @endif
                <div style="font-size: 16px; font-weight: 900; color: #0d9488; margin-top: 4px; border-top: 2px solid #0f172a; padding-top: 4px;">
                    Total: {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
@else
{{-- Web View: Specialized Editorial Layout --}}
<div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-200 dark:border-slate-800 shadow-md">
    
    <!-- Top Bar with Special Badge -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-6 border-b-2 border-slate-900 dark:border-white gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-7 h-7 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs"><i data-lucide="cross" class="w-4 h-4"></i></div>
                <!-- Dedicated Logo Frame -->
                <div class="invoice-logo-frame h-16 w-48 max-w-full flex items-center justify-start overflow-hidden">
                    @if($invoice->logo)
                        <img src="{{ $invoice->logo->url }}" alt="Company Logo" class="max-h-16 max-w-full object-contain">
                    @elseif(!empty($invoice->user->logo_url))
                        <img src="{{ $invoice->user->logo_url }}" alt="Company Logo" class="max-h-16 max-w-full object-contain">
                    @else
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 flex items-center justify-center font-black text-sm">
                                {{ strtoupper(substr($invoice->user->company_name ?? $invoice->user->name ?? 'IH', 0, 2)) }}
                            </div>
                            <span class="font-black text-sm text-slate-900 dark:text-white">{{ $invoice->user->company_name ?? $invoice->user->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
            <p class="text-xs text-slate-500">{{ $invoice->user->email }}</p>
        </div>

        <div class="sm:text-right space-y-1">
            <span class="text-3xl font-black tracking-tight text-slate-900 dark:text-white block">INVOICE</span>
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Clinical & Healthcare Provider Statement</span>
            <div class="mt-2 flex items-center sm:justify-end gap-2">
                <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">#{{ $invoice->invoice_number }}</span>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                    {{ $invoice->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Issued: {{ $invoice->invoice_date->format('M d, Y') }} | Due: {{ $invoice->due_date->format('M d, Y') }}</p>
        </div>
    </div>

    <!-- Client Card & Barcode -->
    <div class="my-6 p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 block mb-1">Billed Entity</span>
            <p class="font-bold text-base text-slate-900 dark:text-white">{{ $invoice->client->name }}</p>
            @if($invoice->client->company_name)<p class="text-xs text-slate-600 dark:text-slate-300 font-medium">{{ $invoice->client->company_name }}</p>@endif
            @if($invoice->client->address)<p class="text-xs text-slate-500">{{ $invoice->client->address }}</p>@endif
        </div>
        <div class="sm:text-right">
            <div class="inline-flex flex-col items-center select-none">
    <svg class="h-8 w-40 text-slate-800 dark:text-slate-300" viewBox="0 0 160 30" fill="currentColor">
        <rect x="0" y="0" width="3" height="28"/><rect x="5" y="0" width="2" height="28"/><rect x="9" y="0" width="4" height="28"/><rect x="16" y="0" width="2" height="28"/><rect x="21" y="0" width="3" height="28"/><rect x="27" y="0" width="6" height="28"/><rect x="36" y="0" width="2" height="28"/><rect x="41" y="0" width="4" height="28"/><rect x="48" y="0" width="3" height="28"/><rect x="54" y="0" width="5" height="28"/><rect x="62" y="0" width="2" height="28"/><rect x="67" y="0" width="4" height="28"/><rect x="74" y="0" width="3" height="28"/><rect x="80" y="0" width="6" height="28"/><rect x="89" y="0" width="2" height="28"/><rect x="94" y="0" width="5" height="28"/><rect x="102" y="0" width="3" height="28"/><rect x="108" y="0" width="4" height="28"/><rect x="115" y="0" width="2" height="28"/><rect x="120" y="0" width="6" height="28"/><rect x="129" y="0" width="3" height="28"/><rect x="135" y="0" width="4" height="28"/><rect x="142" y="0" width="2" height="28"/><rect x="147" y="0" width="5" height="28"/><rect x="155" y="0" width="3" height="28"/>
    </svg>
    <span class="font-mono text-[9px] text-slate-400 tracking-widest mt-0.5">AUTH-VERIFIED</span>
</div>
        </div>
    </div>

    <!-- Table -->
    <div class="my-6 overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-bold uppercase border-b-2 border-slate-900 dark:border-white">
                <tr>
                    <th class="py-3 px-4">Item & Description</th>
                    <th class="py-3 px-4 text-center">Qty</th>
                    <th class="py-3 px-4 text-right">Unit Price</th>
                    <th class="py-3 px-4 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($invoice->items as $item)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                    <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">{{ $item->description }}</td>
                    <td class="py-3.5 px-4 text-center text-slate-600 dark:text-slate-300">{{ $item->quantity }}</td>
                    <td class="py-3.5 px-4 text-right text-slate-600 dark:text-slate-300">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Bottom Stamp, Signatures & Totals -->
    <div class="pt-6 border-t-2 border-slate-900 dark:border-white flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="inline-block transform -rotate-12 select-none opacity-85 hover:opacity-100 transition-opacity">
    <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="50" r="46" stroke="#0d9488" stroke-width="2" stroke-dasharray="3 2" />
        <circle cx="50" cy="50" r="40" stroke="#0d9488" stroke-width="1.5" />
        <circle cx="50" cy="50" r="16" fill="#0d9488" fill-opacity="0.08" stroke="#0d9488" stroke-width="1" />
        <text x="50" y="44" font-size="6.5" font-weight="900" text-anchor="middle" fill="#0d9488" letter-spacing="1">VERIFIED</text>
        <text x="50" y="54" font-size="8" font-weight="900" text-anchor="middle" fill="#0d9488" letter-spacing="0.5">VERIFIED</text>
        <text x="50" y="63" font-size="5.5" font-weight="800" text-anchor="middle" fill="#0d9488">★ AUTHENTIC ★</text>
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

        <div class="w-full sm:w-72 bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 space-y-2 text-xs">
            <div class="flex justify-between text-slate-500">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->tax_amount > 0)
            <div class="flex justify-between text-slate-500">
                <span>Tax:</span>
                <span class="font-bold">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between items-baseline text-sm">
                <span class="font-black text-slate-900 dark:text-white uppercase tracking-wider">Total:</span>
                <span class="text-xl font-black text-slate-900 dark:text-white" style="color: #0d9488;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endif