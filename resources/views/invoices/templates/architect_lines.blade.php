@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Consolas', 'Courier New', monospace; }
        body { color: #0f172a; background: #ffffff; font-size: 11px; line-height: 1.4; padding: 25px; }
        .blueprint-frame { border: 2px solid #1d4ed8; padding: 18px; }
        .badge { display: inline-block; padding: 2px 6px; font-size: 9px; font-weight: 900; background: #1d4ed8; color: #ffffff; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; border: 1px solid #cbd5e1; }
        .table th { background: #f1f5f9; border: 1px solid #cbd5e1; padding: 6px 5px; text-align: left; font-size: 9px; font-weight: 900; text-transform: uppercase; }
        .table td { border: 1px solid #e2e8f0; padding: 7px 5px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="blueprint-frame">
        <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 15px;">
            <tr>
                <td style="vertical-align: top; width: 60%;">
                    <div style="font-size: 8px; font-weight: 900; color: #1d4ed8;">[CAD DRAWING / BILLING SHEET: REV-A]</div>
                    <!-- Dedicated Logo Frame -->
                    <div style="height: 50px; max-height: 50px; width: 180px; overflow: hidden; margin: 6px 0;">
                        @if($invoice->logo)
                            <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 46px; max-width: 175px; object-fit: contain; display: block;">
                        @else
                            <div style="font-size: 18px; font-weight: 900; color: #1d4ed8;">{{ strtoupper($invoice->user->company_name ?? $invoice->user->name) }}</div>
                        @endif
                    </div>
                    <div style="color: #64748b; font-size: 10px;">OPERATOR: {{ $invoice->user->email }}</div>
                </td>
                <td style="vertical-align: top; width: 40%; text-align: right;">
                    <span class="badge">{{ strtoupper($invoice->status) }}</span>
                    <div style="font-size: 16px; font-weight: 900; color: #1d4ed8; margin-top: 3px;">REF: {{ $invoice->invoice_number }}</div>
                    <div style="font-size: 10px; color: #64748b;">DATE: {{ $invoice->invoice_date->format('Y-m-d') }}</div>
                    <div style="font-size: 10px; color: #64748b;">DUE: {{ $invoice->due_date->format('Y-m-d') }}</div>
                </td>
            </tr>
        </table>

        <!-- Client & Site Info -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 1px solid #cbd5e1; background: #f8fafc;">
            <tr>
                <td style="padding: 10px; width: 50%; vertical-align: top; border-right: 1px solid #cbd5e1;">
                    <div style="font-size: 8px; font-weight: 900; color: #1d4ed8;">[CONTRACTOR / CLIENT]</div>
                    <div style="font-size: 12px; font-weight: bold; margin-top: 2px;">{{ $invoice->client->name }}</div>
                    @if($invoice->client->company_name)<div style="font-size: 10px;">{{ $invoice->client->company_name }}</div>@endif
                    @if($invoice->client->address)<div style="font-size: 9px; color: #64748b;">{{ $invoice->client->address }}</div>@endif
                </td>
                <td style="padding: 10px; width: 50%; vertical-align: top;">
                    <div style="font-size: 8px; font-weight: 900; color: #1d4ed8;">[SETTLEMENT INSTRUCTIONS]</div>
                    <div style="font-size: 9px; color: #475569; margin-top: 2px;">{{ $invoice->payment_instructions ?? 'Direct settlement via ACH / Corporate wire.' }}</div>
                </td>
            </tr>
        </table>

        <!-- Table -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 55%;">ITEM / SCOPE OF WORK</th>
                    <th style="width: 15%; text-align: center;">UNITS</th>
                    <th style="width: 15%; text-align: right;">RATE</th>
                    <th style="width: 15%; text-align: right;">NET AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td style="font-weight: 600;">{{ $item->description }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Calculations -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="width: 55%; vertical-align: bottom;">
                    <div style="border: 2px dashed #1d4ed8; padding: 6px; width: 180px; text-align: center;">
                        <span style="font-size: 9px; font-weight: 900; color: #1d4ed8;">APPROVED FOR PAYMENT</span>
                    </div>
                </td>
                <td style="width: 45%; vertical-align: top; text-align: right;">
                    <div style="font-size: 11px;">SUBTOTAL: {{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</div>
                    @if($invoice->tax_amount > 0)
                    <div style="font-size: 11px; margin-top: 2px;">TAX: +{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</div>
                    @endif
                    <div style="font-size: 14px; font-weight: 900; color: #1d4ed8; margin-top: 4px; border-top: 2px solid #1d4ed8; padding-top: 4px;">
                        TOTAL: {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
@else
{{-- Web View: Blueprint Grid Engineering --}}
<div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border-2 border-slate-900 dark:border-slate-700 shadow-xl font-mono relative overflow-hidden">
    
    <!-- Crosshairs on corners -->
    <span class="absolute top-2 left-2 text-slate-400 font-mono text-sm">+</span>
    <span class="absolute top-2 right-2 text-slate-400 font-mono text-sm">+</span>
    <span class="absolute bottom-2 left-2 text-slate-400 font-mono text-sm">+</span>
    <span class="absolute bottom-2 right-2 text-slate-400 font-mono text-sm">+</span>

    <!-- Top Technical Specification Bar -->
    <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-6 border-b-2 border-slate-900 dark:border-slate-700 gap-6">
        <div>
            <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest block mb-2">[SPEC_CODE: DWG-INV-2026]</span>
            <!-- Dedicated Logo Frame -->
            <div class="invoice-logo-frame h-16 w-48 max-w-full flex items-center justify-start overflow-hidden mb-2">
                @if($invoice->logo)
                    <img src="{{ $invoice->logo->url }}" alt="Company Logo" class="max-h-16 max-w-full object-contain">
                @elseif(!empty($invoice->user->logo_url))
                    <img src="{{ $invoice->user->logo_url }}" alt="Company Logo" class="max-h-16 max-w-full object-contain">
                @else
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 border-2 border-slate-900 dark:border-white flex items-center justify-center font-black text-sm">
                            {{ strtoupper(substr($invoice->user->company_name ?? $invoice->user->name ?? 'IH', 0, 2)) }}
                        </div>
                        <div>
                            <span class="font-black text-xs text-slate-900 dark:text-white block">{{ $invoice->user->company_name ?? $invoice->user->name }}</span>
                            <span class="text-[9px] text-slate-400">ENGINEERING BILLING</span>
                        </div>
                    </div>
                @endif
            </div>
            <p class="text-xs text-slate-500">{{ $invoice->user->email }}</p>
        </div>

        <div class="sm:text-right space-y-1">
            <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tight block">INVOICE</span>
            <p class="text-[11px] text-slate-400">Architectural Drafting & Elevation Invoice</p>
            <div class="mt-2 flex items-center sm:justify-end gap-2">
                <span class="font-bold text-xs bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 px-2 py-0.5 border border-slate-300 dark:border-slate-700">
                    REF: {{ $invoice->invoice_number }}
                </span>
                <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-blue-600 text-white">
                    {{ $invoice->status }}
                </span>
            </div>
            <div class="text-xs text-slate-400 mt-1">
                <span>DATE: {{ $invoice->invoice_date->format('Y-m-d') }}</span> | <span>DUE: {{ $invoice->due_date->format('Y-m-d') }}</span>
            </div>
        </div>
    </div>

    <!-- Client / Project Grid -->
    <div class="my-6 grid grid-cols-1 md:grid-cols-2 gap-4 border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 p-4 rounded-xl text-xs">
        <div>
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">// CLIENT_REPRESENTATIVE</span>
            <p class="font-bold text-sm text-slate-900 dark:text-white">{{ $invoice->client->name }}</p>
            @if($invoice->client->company_name)<p class="text-slate-600 dark:text-slate-300">{{ $invoice->client->company_name }}</p>@endif
            @if($invoice->client->address)<p class="text-slate-500">{{ $invoice->client->address }}</p>@endif
        </div>
        <div class="md:text-right flex flex-col md:items-end justify-center">
            <div class="inline-flex flex-col items-center select-none">
    <svg class="h-8 w-40 text-slate-800 dark:text-slate-300" viewBox="0 0 160 30" fill="currentColor">
        <rect x="0" y="0" width="3" height="28"/><rect x="5" y="0" width="2" height="28"/><rect x="9" y="0" width="4" height="28"/><rect x="16" y="0" width="2" height="28"/><rect x="21" y="0" width="3" height="28"/><rect x="27" y="0" width="6" height="28"/><rect x="36" y="0" width="2" height="28"/><rect x="41" y="0" width="4" height="28"/><rect x="48" y="0" width="3" height="28"/><rect x="54" y="0" width="5" height="28"/><rect x="62" y="0" width="2" height="28"/><rect x="67" y="0" width="4" height="28"/><rect x="74" y="0" width="3" height="28"/><rect x="80" y="0" width="6" height="28"/><rect x="89" y="0" width="2" height="28"/><rect x="94" y="0" width="5" height="28"/><rect x="102" y="0" width="3" height="28"/><rect x="108" y="0" width="4" height="28"/><rect x="115" y="0" width="2" height="28"/><rect x="120" y="0" width="6" height="28"/><rect x="129" y="0" width="3" height="28"/><rect x="135" y="0" width="4" height="28"/><rect x="142" y="0" width="2" height="28"/><rect x="147" y="0" width="5" height="28"/><rect x="155" y="0" width="3" height="28"/>
    </svg>
    <span class="font-mono text-[9px] text-slate-400 tracking-widest mt-0.5">AUTH-VERIFIED</span>
</div>
        </div>
    </div>

    <!-- Engineering Boxed Grid Table -->
    <div class="my-6 overflow-x-auto">
        <table class="w-full text-left text-xs border border-slate-200 dark:border-slate-700">
            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700">ITEM SPECIFICATION</th>
                    <th class="py-2.5 px-3 text-center border-r border-slate-200 dark:border-slate-700 w-20">UNITS</th>
                    <th class="py-2.5 px-3 text-right border-r border-slate-200 dark:border-slate-700 w-32">RATE</th>
                    <th class="py-2.5 px-3 text-right w-36">AMOUNT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                @foreach($invoice->items as $item)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                    <td class="py-3 px-3 font-semibold text-slate-900 dark:text-white border-r border-slate-200 dark:border-slate-700">{{ $item->description }}</td>
                    <td class="py-3 px-3 text-center text-slate-600 dark:text-slate-300 border-r border-slate-200 dark:border-slate-700">{{ $item->quantity }}</td>
                    <td class="py-3 px-3 text-right text-slate-600 dark:text-slate-300 border-r border-slate-200 dark:border-slate-700">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-3 px-3 text-right font-bold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Bottom Hazard/Engineering Total Bar -->
    <div class="pt-6 border-t-2 border-slate-900 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
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
            <div class="border-2 border-dashed border-slate-400 p-2 text-center text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">
                APPROVED FOR PAYMENT
            </div>
        </div>

        <div class="w-full sm:w-72 border-2 border-slate-900 dark:border-slate-600 p-4 space-y-2 text-xs bg-slate-50 dark:bg-slate-800">
            <div class="flex justify-between text-slate-500">
                <span>SUBTOTAL:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->tax_amount > 0)
            <div class="flex justify-between text-slate-500">
                <span>TAX LEVY:</span>
                <span class="font-bold">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="pt-2 border-t-2 border-slate-900 dark:border-slate-600 flex justify-between items-baseline text-sm">
                <span class="font-black text-slate-900 dark:text-white">NET BALANCE:</span>
                <span class="text-xl font-black text-blue-600 dark:text-blue-400">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endif