@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Courier New', Courier, monospace; }
        body { color: #f8fafc; background: #020617; font-size: 12px; line-height: 1.4; padding: 30px; }
        .terminal-header { border-bottom: 2px solid #06b6d4; padding-bottom: 16px; margin-bottom: 22px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 2px; font-size: 10px; font-weight: 800; text-transform: uppercase; background: #06b6d4; color: #020617; }
        .code-box { border: 1px dashed #06b6d4; padding: 12px; border-radius: 4px; background: rgba(15, 23, 42, 0.6); margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th { border-bottom: 2px solid #06b6d4; padding: 8px 6px; text-align: left; font-size: 10px; color: #06b6d4; text-transform: uppercase; }
        .table td { border-bottom: 1px solid #1e293b; padding: 9px 6px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="terminal-header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: top; width: 60%;">
                    <div style="font-size: 10px; color: #06b6d4; font-weight: 800; letter-spacing: 1px;">[SYSTEM_PROTOCOL: INVOICE_V2]</div>
                    <!-- Dedicated Logo Frame -->
                    <div style="height: 55px; max-height: 55px; width: 180px; overflow: hidden; margin: 8px 0;">
                        @if($invoice->logo)
                            <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 50px; max-width: 175px; object-fit: contain; display: block;">
                        @else
                            <div style="font-size: 20px; font-weight: 900; color: #ffffff;">&gt; {{ strtoupper($invoice->user->company_name ?? $invoice->user->name) }}_</div>
                        @endif
                    </div>
                    <div style="color: #94a3b8; font-size: 11px;">AUTH_HASH: {{ substr(md5($invoice->invoice_number), 0, 16) }}</div>
                </td>
                <td style="vertical-align: top; width: 40%; text-align: right;">
                    <div style="font-size: 24px; font-weight: 900; color: #06b6d4;">#{{ $invoice->invoice_number }}</div>
                    <div style="margin-top: 4px;"><span class="badge">{{ strtoupper($invoice->status) }}</span></div>
                    <div style="color: #94a3b8; font-size: 10px; margin-top: 5px;">TIMESTAMP: {{ $invoice->invoice_date->format('Y-m-d') }}</div>
                    <div style="color: #94a3b8; font-size: 10px;">EXPIRY: {{ $invoice->due_date->format('Y-m-d') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Client Node -->
    <div class="code-box">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <div style="font-size: 9px; color: #06b6d4; text-transform: uppercase; font-weight: bold;">[CLIENT_NODE]</div>
                    <div style="font-size: 13px; font-weight: bold; color: #ffffff; margin-top: 3px;">{{ $invoice->client->name }}</div>
                    @if($invoice->client->company_name)<div style="color: #cbd5e1; font-size: 11px;">{{ $invoice->client->company_name }}</div>@endif
                    @if($invoice->client->email)<div style="color: #94a3b8; font-size: 10px;">{{ $invoice->client->email }}</div>@endif
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <div style="font-size: 9px; color: #06b6d4; text-transform: uppercase; font-weight: bold;">[SETTLEMENT_PARAMETERS]</div>
                    <div style="color: #cbd5e1; font-size: 11px; margin-top: 3px;">CURRENCY: {{ $invoice->currency }}</div>
                    <div style="color: #94a3b8; font-size: 10px;">PAY_CHANNEL: WIRE / STRIPE CRYPTO</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 55%;">// INSTRUCTION / SPECIFICATION</th>
                <th style="width: 15%; text-align: center;">QTY</th>
                <th style="width: 15%; text-align: right;">UNIT</th>
                <th style="width: 15%; text-align: right;">NET</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="color: #f1f5f9;">{{ $item->description }}</td>
                <td style="text-align: center; color: #94a3b8;">{{ $item->quantity }}</td>
                <td style="text-align: right; color: #94a3b8;">{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right; color: #06b6d4; font-weight: bold;">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-top: 25px;">
        <tr>
            <td style="width: 60%; vertical-align: middle;">
                <div style="color: #06b6d4; font-size: 9px;">*** CRYPTOGRAPHICALLY VALIDATED INVOICE SPECIFICATION ***</div>
                <div style="color: #64748b; font-size: 9px; margin-top: 3px;">NODE_ID: {{ strtoupper(substr(md5($invoice->id), 0, 12)) }}</div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <div style="font-size: 12px; color: #94a3b8;">SUBTOTAL: {{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</div>
                @if($invoice->tax_amount > 0)
                <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">TAX_LEVY: +{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</div>
                @endif
                <div style="font-size: 16px; font-weight: 900; color: #06b6d4; margin-top: 6px; border-top: 1px solid #06b6d4; padding-top: 6px;">
                    TOTAL: {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
@else
{{-- Web View: High-Tech Terminal Window --}}
<div class="bg-slate-950 text-slate-100 rounded-3xl p-6 sm:p-10 font-mono border border-slate-800 shadow-2xl relative overflow-hidden">
    <!-- Neon Top Accent Glow -->
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-cyan-500 via-emerald-400 to-indigo-500"></div>

    <!-- Terminal Window Top Control Bar -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 mb-6 text-xs">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
            <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
            <span class="text-slate-400 ml-2 text-[11px]">// terminal: inv-daemon --ref={{ $invoice->invoice_number }}</span>
        </div>
        <div class="text-[11px] text-emerald-400 font-bold flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            LIVE CONTRACT
        </div>
    </div>

    <!-- Header Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-slate-800">
        <div>
            <!-- Dedicated Logo Frame -->
            <div class="invoice-logo-frame h-16 w-48 max-w-full flex items-center justify-start overflow-hidden mb-3 p-1.5 rounded-xl bg-slate-900/60 border border-slate-800">
                @if($invoice->logo)
                    <img src="{{ $invoice->logo->url }}" alt="Company Logo" class="max-h-14 max-w-full object-contain">
                @elseif(!empty($invoice->user->logo_url))
                    <img src="{{ $invoice->user->logo_url }}" alt="Company Logo" class="max-h-14 max-w-full object-contain">
                @else
                    <div class="flex items-center gap-2 text-cyan-400 font-black text-sm">
                        <i data-lucide="terminal" class="w-5 h-5"></i>
                        <span>{{ strtoupper($invoice->user->company_name ?? $invoice->user->name) }}</span>
                    </div>
                @endif
            </div>
            <p class="text-xs text-slate-400">{{ $invoice->user->email }}</p>
            <p class="text-[10px] text-cyan-400 mt-1 font-semibold">HASH: {{ substr(md5($invoice->invoice_number), 0, 20) }}...</p>
        </div>

        <div class="md:text-right space-y-1">
            <div class="text-2xl sm:text-3xl font-black text-cyan-400">#{{ $invoice->invoice_number }}</div>
            <span class="inline-block px-3 py-1 rounded bg-cyan-950/80 text-cyan-300 border border-cyan-800/80 text-[10px] font-black uppercase tracking-wider">
                STATUS: {{ $invoice->status }}
            </span>
            <div class="text-xs text-slate-400 pt-2">
                <span>DATE: {{ $invoice->invoice_date->format('Y-m-d') }}</span> | <span>DUE: {{ $invoice->due_date->format('Y-m-d') }}</span>
            </div>
        </div>
    </div>

    <!-- Target Node Info Card -->
    <div class="my-6 p-4 rounded-2xl bg-slate-900/70 border border-slate-800 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div>
            <span class="text-[10px] text-cyan-400 font-bold uppercase tracking-wider block mb-1"># CLIENT_ENTITY</span>
            <p class="font-bold text-white text-sm">{{ $invoice->client->name }}</p>
            @if($invoice->client->company_name)<p class="text-slate-300">{{ $invoice->client->company_name }}</p>@endif
            @if($invoice->client->email)<p class="text-slate-400">{{ $invoice->client->email }}</p>@endif
        </div>
        <div class="sm:text-right flex flex-col sm:items-end justify-center">
            <span class="text-[10px] text-cyan-400 font-bold uppercase tracking-wider block mb-1"># VERIFICATION MATRIX</span>
            <div class="inline-flex flex-col items-center select-none">
    <svg class="h-8 w-40 text-slate-800 dark:text-slate-300" viewBox="0 0 160 30" fill="currentColor">
        <rect x="0" y="0" width="3" height="28"/><rect x="5" y="0" width="2" height="28"/><rect x="9" y="0" width="4" height="28"/><rect x="16" y="0" width="2" height="28"/><rect x="21" y="0" width="3" height="28"/><rect x="27" y="0" width="6" height="28"/><rect x="36" y="0" width="2" height="28"/><rect x="41" y="0" width="4" height="28"/><rect x="48" y="0" width="3" height="28"/><rect x="54" y="0" width="5" height="28"/><rect x="62" y="0" width="2" height="28"/><rect x="67" y="0" width="4" height="28"/><rect x="74" y="0" width="3" height="28"/><rect x="80" y="0" width="6" height="28"/><rect x="89" y="0" width="2" height="28"/><rect x="94" y="0" width="5" height="28"/><rect x="102" y="0" width="3" height="28"/><rect x="108" y="0" width="4" height="28"/><rect x="115" y="0" width="2" height="28"/><rect x="120" y="0" width="6" height="28"/><rect x="129" y="0" width="3" height="28"/><rect x="135" y="0" width="4" height="28"/><rect x="142" y="0" width="2" height="28"/><rect x="147" y="0" width="5" height="28"/><rect x="155" y="0" width="3" height="28"/>
    </svg>
    <span class="font-mono text-[9px] text-slate-400 tracking-widest mt-0.5">AUTH-VERIFIED</span>
</div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="overflow-x-auto my-6">
        <table class="w-full text-left text-xs">
            <thead class="text-cyan-400 font-bold uppercase border-b border-cyan-900/60 pb-2">
                <tr>
                    <th class="py-2.5 px-3"># ITEM SPECIFICATION</th>
                    <th class="py-2.5 px-3 text-center">QTY</th>
                    <th class="py-2.5 px-3 text-right">UNIT PRICE</th>
                    <th class="py-2.5 px-3 text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @foreach($invoice->items as $item)
                <tr class="hover:bg-slate-900/40 transition">
                    <td class="py-3 px-3 text-slate-200 font-medium">{{ $item->description }}</td>
                    <td class="py-3 px-3 text-center text-slate-400">{{ $item->quantity }}</td>
                    <td class="py-3 px-3 text-right text-slate-400">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-3 px-3 text-right font-bold text-cyan-400">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Total & Verification Footer -->
    <div class="pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-6">
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
            <div>
                <span class="text-xs font-bold text-cyan-400 block">// AUTHENTICATED PAYLOAD</span>
                <span class="text-[10px] text-slate-400">Cryptographically signed digital ledger document.</span>
            </div>
        </div>

        <div class="w-full sm:w-72 bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-2 text-xs">
            <div class="flex justify-between text-slate-400">
                <span>SUBTOTAL:</span>
                <span class="text-slate-200">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="flex justify-between text-emerald-400">
                <span>DISCOUNT:</span>
                <span>-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="flex justify-between text-slate-400">
                <span>TAX_LEVY:</span>
                <span>+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="pt-2 border-t border-slate-800 flex justify-between items-baseline text-sm">
                <span class="font-black text-cyan-400">NET DUE:</span>
                <span class="text-xl font-black text-cyan-300">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endif