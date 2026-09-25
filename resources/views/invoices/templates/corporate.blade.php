@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Georgia', Times, serif; }
        body { color: #1e293b; background: #ffffff; font-size: 12px; line-height: 1.5; padding: 25px; }
        .certificate-border { border: 3px double #1e3a8a; padding: 25px; }
        .badge { display: inline-block; padding: 4px 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; background: #1e3a8a; color: #ffffff; letter-spacing: 1px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { border-bottom: 2px solid #1e3a8a; border-top: 1px solid #1e3a8a; padding: 7px 5px; text-align: left; font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #1e3a8a; }
        .table td { border-bottom: 1px solid #e2e8f0; padding: 8px 5px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="certificate-border">
        <!-- Header -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="vertical-align: top; width: 55%;">
                    <!-- Dedicated Logo Frame -->
                    <div style="height: 55px; max-height: 55px; width: 180px; overflow: hidden; margin-bottom: 8px;">
                        @if($invoice->logo)
                            <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 50px; max-width: 175px; object-fit: contain; display: block;">
                        @else
                            <div style="font-size: 20px; font-weight: 800; color: #1e3a8a; letter-spacing: 1px;">{{ strtoupper($invoice->user->company_name ?? $invoice->user->name) }}</div>
                            <div style="font-size: 8px; color: #b45309; text-transform: uppercase; letter-spacing: 2px;">Official Financial Instrument</div>
                        @endif
                    </div>
                    <div style="font-size: 11px; color: #475569;">{{ $invoice->user->email }}</div>
                </td>
                <td style="vertical-align: top; width: 45%; text-align: right;">
                    <div style="font-size: 22px; font-weight: 900; color: #1e3a8a; letter-spacing: 2px;">INVOICE</div>
                    <div style="font-size: 9px; color: #64748b; font-style: italic; margin-bottom: 4px;">Official Corporate Settlement Notice</div>
                    <span class="badge">{{ strtoupper($invoice->status) }}</span>
                    <div style="font-size: 12px; font-weight: bold; margin-top: 5px;">No. {{ $invoice->invoice_number }}</div>
                    <div style="font-size: 10px; color: #64748b;">Issued: {{ $invoice->invoice_date->format('F d, Y') }}</div>
                    <div style="font-size: 10px; color: #64748b;">Maturity Date: {{ $invoice->due_date->format('F d, Y') }}</div>
                </td>
            </tr>
        </table>

        <!-- Billed Party -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; margin-bottom: 18px;">
            <div style="font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #b45309; margin-bottom: 2px;">Billed Debtor Entity</div>
            <div style="font-size: 13px; font-weight: bold; color: #1e3a8a;">{{ $invoice->client->name }}</div>
            @if($invoice->client->company_name)<div style="font-size: 11px; color: #334155;">{{ $invoice->client->company_name }}</div>@endif
            @if($invoice->client->address)<div style="font-size: 10px; color: #64748b;">{{ $invoice->client->address }}</div>@endif
        </div>

        <!-- Table -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 55%;">Itemized Record / Services</th>
                    <th style="width: 15%; text-align: center;">Units</th>
                    <th style="width: 15%; text-align: right;">Price</th>
                    <th style="width: 15%; text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td style="color: #0f172a; font-weight: 600;">{{ $item->description }}</td>
                    <td style="text-align: center; color: #475569;">{{ $item->quantity }}</td>
                    <td style="text-align: right; color: #475569;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary & Seals -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 25px;">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    <div style="font-size: 10px; font-style: italic; color: #64748b;">This instrument constitutes a formal financial settlement record.</div>
                    <div style="margin-top: 15px; border-top: 1px solid #cbd5e1; width: 160px; padding-top: 4px; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #475569;">
                        Authorized Seal & Signature
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="text-align: right; padding: 2px 0; color: #64748b;">Subtotal Amount:</td>
                            <td style="text-align: right; padding: 2px 0; font-weight: bold; width: 40%;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->tax_amount > 0)
                        <tr>
                            <td style="text-align: right; padding: 2px 0; color: #64748b;">Applicable Tax:</td>
                            <td style="text-align: right; padding: 2px 0; font-weight: bold;">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr style="border-top: 2px solid #1e3a8a; border-bottom: 2px solid #1e3a8a;">
                            <td style="text-align: right; padding: 6px 0; font-size: 13px; font-weight: 900; color: #1e3a8a;">Total Due:</td>
                            <td style="text-align: right; padding: 6px 0; font-size: 14px; font-weight: 900; color: #1e3a8a;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
@else
{{-- Web View: Executive Formal Certificate Architecture --}}
<div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-12 border-4 border-double border-slate-300 dark:border-slate-700 shadow-xl relative overflow-hidden">
    
    <!-- Ornate SVG Corner Brackets -->
    <svg class="w-8 h-8 text-amber-600/60 dark:text-amber-400/60 absolute top-4 left-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M 2 14 L 2 2 L 14 2" /><circle cx="5" cy="5" r="1.5" fill="currentColor"/>
    </svg>
    <svg class="w-8 h-8 text-amber-600/60 dark:text-amber-400/60 absolute top-4 right-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M 22 14 L 22 2 L 10 2" /><circle cx="19" cy="5" r="1.5" fill="currentColor"/>
    </svg>
    <svg class="w-8 h-8 text-amber-600/60 dark:text-amber-400/60 absolute bottom-4 left-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M 2 10 L 2 22 L 14 22" /><circle cx="5" cy="19" r="1.5" fill="currentColor"/>
    </svg>
    <svg class="w-8 h-8 text-amber-600/60 dark:text-amber-400/60 absolute bottom-4 right-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M 22 10 L 22 22 L 10 22" /><circle cx="19" cy="19" r="1.5" fill="currentColor"/>
    </svg>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-8 border-b-2 border-slate-200 dark:border-slate-800 gap-6">
        <div>
            <!-- Dedicated Logo Frame -->
            <div class="invoice-logo-frame h-16 w-48 max-w-full flex items-center justify-start overflow-hidden mb-3">
                @if($invoice->logo)
                    <img src="{{ $invoice->logo->url }}" alt="Company Logo" class="max-h-16 max-w-full object-contain">
                @elseif(!empty($invoice->user->logo_url))
                    <img src="{{ $invoice->user->logo_url }}" alt="Company Logo" class="max-h-16 max-w-full object-contain">
                @else
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 flex items-center justify-center font-serif font-black text-xl shadow-md border-2 border-amber-500/40">
                            {{ strtoupper(substr($invoice->user->company_name ?? $invoice->user->name ?? 'IH', 0, 2)) }}
                        </div>
                        <div>
                            <span class="font-serif font-black text-base text-slate-900 dark:text-white block">{{ $invoice->user->company_name ?? $invoice->user->name }}</span>
                            <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest">Formal Financial Record</span>
                        </div>
                    </div>
                @endif
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-serif">{{ $invoice->user->email }}</p>
        </div>

        <div class="sm:text-right space-y-1">
            <span class="font-serif text-3xl font-black tracking-widest text-slate-900 dark:text-white block">INVOICE</span>
            <p class="text-[11px] font-serif italic text-slate-500 dark:text-slate-400">Official Corporate Settlement Notice</p>
            <div class="mt-2 flex items-center sm:justify-end gap-2">
                <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">№ {{ $invoice->invoice_number }}</span>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                    {{ $invoice->status }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1 font-serif">Issued: {{ $invoice->invoice_date->format('F d, Y') }} | Due: {{ $invoice->due_date->format('F d, Y') }}</p>
        </div>
    </div>

    <!-- Client Card -->
    <div class="my-6 p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-700 dark:text-amber-400 block mb-1">Billed To Authorized Debtor</span>
            <h3 class="font-serif font-bold text-base text-slate-900 dark:text-white">{{ $invoice->client->name }}</h3>
            @if($invoice->client->company_name)<p class="text-xs text-slate-600 dark:text-slate-300">{{ $invoice->client->company_name }}</p>@endif
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

    <!-- Ledger Table -->
    <div class="my-6 overflow-x-auto">
        <table class="w-full text-left text-xs font-serif">
            <thead class="border-y-2 border-slate-900 dark:border-slate-200 font-bold uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                <tr>
                    <th class="py-3 px-4">Itemized Particulars</th>
                    <th class="py-3 px-4 text-center">Quantity</th>
                    <th class="py-3 px-4 text-right">Unit Rate</th>
                    <th class="py-3 px-4 text-right">Total Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($invoice->items as $item)
                <tr>
                    <td class="py-3.5 px-4 font-medium text-slate-900 dark:text-white">{{ $item->description }}</td>
                    <td class="py-3.5 px-4 text-center text-slate-600 dark:text-slate-300">{{ $item->quantity }}</td>
                    <td class="py-3.5 px-4 text-right text-slate-600 dark:text-slate-300">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Bottom Verification & Dual Signatures -->
    <div class="pt-6 border-t-2 border-slate-900 dark:border-slate-200 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="inline-block transform -rotate-12 select-none opacity-85 hover:opacity-100 transition-opacity">
    <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="50" r="46" stroke="#b45309" stroke-width="2" stroke-dasharray="3 2" />
        <circle cx="50" cy="50" r="40" stroke="#b45309" stroke-width="1.5" />
        <circle cx="50" cy="50" r="16" fill="#b45309" fill-opacity="0.08" stroke="#b45309" stroke-width="1" />
        <text x="50" y="44" font-size="6.5" font-weight="900" text-anchor="middle" fill="#b45309" letter-spacing="1">CERTIFIED RECORD</text>
        <text x="50" y="54" font-size="8" font-weight="900" text-anchor="middle" fill="#b45309" letter-spacing="0.5">VERIFIED</text>
        <text x="50" y="63" font-size="5.5" font-weight="800" text-anchor="middle" fill="#b45309">★ AUTHENTIC ★</text>
    </svg>
</div>
            <div class="font-serif">
                <span class="text-xs font-bold text-slate-900 dark:text-white block">Official Issuance Certification</span>
                <span class="text-[11px] text-slate-500">Seal affixed by authorized financial signatory.</span>
            </div>
        </div>

        <div class="flex items-center gap-6">
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

            <div class="w-64 bg-slate-50 dark:bg-slate-800/80 p-4 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-serif space-y-1.5">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-slate-500">
                    <span>Tax:</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="pt-2 border-t-2 border-double border-slate-900 dark:border-slate-200 flex justify-between items-baseline text-sm">
                    <span class="font-black text-slate-900 dark:text-white uppercase tracking-wider">Settlement Due:</span>
                    <span class="text-lg font-black text-amber-700 dark:text-amber-400">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif