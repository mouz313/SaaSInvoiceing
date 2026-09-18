@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, monospace; }
        body { color: #0f172a; background: #ffffff; font-size: 12px; line-height: 1.4; padding: 30px 35px; }
        .brand-h1 { font-size: 20px; font-weight: 900; letter-spacing: -0.5px; text-transform: uppercase; }
        .section-label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 6px; }
        table.grid-data-table { width: 100%; border-collapse: collapse; border: 2px solid #0f172a; margin-bottom: 25px; }
        table.grid-data-table th { background: #0f172a; color: #ffffff; padding: 9px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        table.grid-data-table td { padding: 9px 10px; border: 1px solid #cbd5e1; }
        .grand-line { background: #0f172a; color: #ffffff; font-weight: bold; font-size: 13px; }
    </style>
</head>
<body>
    {{-- Header Box --}}
    <table style="width: 100%; border-collapse: collapse; border: 2px solid #0f172a; margin-bottom: 25px;">
        <tr>
            <td style="vertical-align: top; padding: 18px 20px; border-right: 2px solid #0f172a;">
                @if($invoice->logo)
                    <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 38px; max-width: 140px; margin-bottom: 6px; display: block;">
                @endif
                <div class="brand-h1">{{ $invoice->user->name }}</div>
                <div style="color: #64748b; font-size: 11px; margin-top: 3px;">{{ $invoice->user->email }}</div>
                <div style="display: inline-block; margin-top: 8px; padding: 2px 8px; border: 1px solid #0f172a; font-weight: bold; font-size: 10px; text-transform: uppercase;">
                    Status: {{ $invoice->status }}
                </div>
            </td>
            <td style="width: 260px; vertical-align: top; padding: 18px 20px; background: #f8fafc;">
                <div style="font-size: 10px; font-weight: bold; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">INVOICE STATEMENT</div>
                <div style="font-size: 18px; font-weight: bold; margin-top: 4px; color: #0f172a;">#{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 8px; font-size: 11px; color: #334155;"><strong>Currency:</strong> {{ strtoupper($invoice->currency) }}</div>
            </td>
        </tr>
    </table>

    {{-- Sub Box --}}
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #cbd5e1; margin-bottom: 25px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding: 14px 16px; border-right: 1px solid #cbd5e1;">
                <div class="section-label">01 // CLIENT IDENTIFICATION</div>
                <div style="font-weight: bold; font-size: 13px; color: #0f172a;">{{ $invoice->client->name }}</div>
                @if($invoice->client->company_name)
                    <div style="color: #334155; margin-top: 2px;">{{ $invoice->client->company_name }}</div>
                @endif
                @if($invoice->client->address)
                    <div style="color: #64748b; margin-top: 2px;">{{ $invoice->client->address }}</div>
                @endif
                @if($invoice->client->city || $invoice->client->country)
                    <div style="color: #64748b; margin-top: 2px;">{{ $invoice->client->city }}, {{ $invoice->client->country }}</div>
                @endif
                @if($invoice->client->tax_id)
                    <div style="color: #64748b; font-size: 11px; margin-top: 2px;">TAX ID: {{ $invoice->client->tax_id }}</div>
                @endif
            </td>
            <td style="width: 50%; vertical-align: top; padding: 14px 16px;">
                <div class="section-label">02 // TRANSACTION TIMELINES</div>
                <div style="margin-top: 2px;"><strong>Issue Timestamp:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</div>
                <div style="margin-top: 4px;"><strong>Maturity / Due:</strong> {{ $invoice->due_date->format('Y-m-d') }}</div>
                <div style="margin-top: 4px;"><strong>Engine Model:</strong> Clean-Grid v1.0</div>
            </td>
        </tr>
    </table>

    {{-- Line Items Table --}}
    <table class="grid-data-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 50%;">Item Description</th>
                <th style="width: 15%; text-align: right;">Units</th>
                <th style="width: 15%; text-align: right;">Unit Rate</th>
                <th style="width: 15%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td style="color: #64748b;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td><strong>{{ $item->description }}</strong></td>
                <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Bottom Box --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
        <tr>
            <td style="vertical-align: top; padding: 14px 16px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 11px;">
                <div class="section-label">03 // REMITTANCE DETAILS</div>
                @if($invoice->payment_instructions)
                    <div style="margin-bottom: 5px;">{{ $invoice->payment_instructions }}</div>
                @endif
                @if($invoice->notes)
                    <div>{{ $invoice->notes }}</div>
                @endif
            </td>
            <td style="width: 25px;"></td>
            <td style="width: 320px; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; border: 2px solid #0f172a;">
                    <tr>
                        <td style="padding: 7px 12px; color: #64748b; border-bottom: 1px solid #cbd5e1;">Subtotal:</td>
                        <td style="padding: 7px 12px; text-align: right; font-weight: bold; border-bottom: 1px solid #cbd5e1;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td style="padding: 7px 12px; color: #dc2626; border-bottom: 1px solid #cbd5e1;">Discount ({{ $invoice->discount_rate }}%):</td>
                        <td style="padding: 7px 12px; text-align: right; font-weight: bold; color: #dc2626; border-bottom: 1px solid #cbd5e1;">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->tax_amount > 0)
                    <tr>
                        <td style="padding: 7px 12px; color: #64748b; border-bottom: 1px solid #cbd5e1;">Tax ({{ $invoice->tax_rate }}%):</td>
                        <td style="padding: 7px 12px; text-align: right; font-weight: bold; border-bottom: 1px solid #cbd5e1;">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if(!empty($invoice->additional_charges))
                        @foreach($invoice->additional_charges as $charge)
                        <tr>
                            <td style="padding: 7px 12px; color: #64748b; border-bottom: 1px solid #cbd5e1;">{{ $charge['name'] }} ({{ $charge['type'] === 'percentage' ? $charge['value'].'%' : 'Fixed' }}):</td>
                            <td style="padding: 7px 12px; text-align: right; font-weight: bold; border-bottom: 1px solid #cbd5e1;">+{{ $invoice->currency }} {{ number_format($charge['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    @endif
                    <tr class="grand-line">
                        <td style="padding: 9px 12px; color: #ffffff;">TOTAL DUE:</td>
                        <td style="padding: 9px 12px; text-align: right; color: #ffffff;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
@else
{{-- Web View: Clean Grid Style --}}
<div class="p-4 sm:p-8 md:p-12 text-slate-900 dark:text-slate-100 font-sans">
    <!-- Grid Header Box -->
    <div class="border-2 border-slate-900 dark:border-slate-300 flex flex-col sm:flex-row mb-6">
        <div class="flex-1 p-6 border-b sm:border-b-0 sm:border-r-2 border-slate-900 dark:border-slate-300">
            @if($invoice->logo)
                <img src="{{ $invoice->logo->url }}" alt="Logo" class="max-h-10 max-w-[140px] object-contain mb-3">
            @endif
            <h1 class="text-xl sm:text-2xl font-mono font-black uppercase tracking-tight text-slate-900 dark:text-white">
                {{ $invoice->user->name }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-mono">{{ $invoice->user->email }}</p>
            <div class="mt-3">
                <span class="inline-block border border-slate-900 dark:border-slate-300 px-2 py-0.5 text-[10px] font-mono font-bold uppercase tracking-widest
                    @if($invoice->status === 'paid') bg-emerald-50 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300
                    @elseif($invoice->status === 'sent') bg-blue-50 text-blue-800 dark:bg-blue-950 dark:text-blue-300
                    @elseif($invoice->status === 'overdue') bg-rose-50 text-rose-800 dark:bg-rose-950 dark:text-rose-300
                    @else bg-slate-50 text-slate-800 dark:bg-slate-800 dark:text-slate-300 @endif">
                    STATUS // {{ strtoupper($invoice->status) }}
                </span>
            </div>
        </div>
        <div class="w-full sm:w-72 p-6 bg-slate-50 dark:bg-slate-800/50 font-mono text-xs space-y-1">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">INVOICE STATEMENT</span>
            <span class="text-xl font-bold text-slate-900 dark:text-white block mt-0.5">#{{ $invoice->invoice_number }}</span>
            <p class="text-slate-600 dark:text-slate-400 pt-2"><strong class="text-slate-800 dark:text-slate-200">CURRENCY:</strong> {{ strtoupper($invoice->currency) }}</p>
        </div>
    </div>

    <!-- Sub-Box for Client & Timelines -->
    <div class="border border-slate-300 dark:border-slate-700 flex flex-col sm:flex-row mb-6 font-mono text-xs">
        <div class="flex-1 p-5 border-b sm:border-b-0 sm:border-r border-slate-300 dark:border-slate-700">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 block mb-2">01 // CLIENT IDENTIFICATION</span>
            <p class="font-bold text-sm text-slate-900 dark:text-white font-sans">{{ $invoice->client->name }}</p>
            @if($invoice->client->company_name)
                <p class="text-slate-700 dark:text-slate-300 text-xs font-sans mt-0.5">{{ $invoice->client->company_name }}</p>
            @endif
            @if($invoice->client->address)
                <p class="text-slate-500 dark:text-slate-400 mt-1 font-sans text-xs">{{ $invoice->client->address }}</p>
            @endif
            @if($invoice->client->city || $invoice->client->country)
                <p class="text-slate-500 dark:text-slate-400 font-sans text-xs">{{ $invoice->client->city }}{{ $invoice->client->city && $invoice->client->country ? ', ' : '' }}{{ $invoice->client->country }}</p>
            @endif
            @if($invoice->client->tax_id)
                <p class="text-slate-500 pt-1">TAX ID: {{ $invoice->client->tax_id }}</p>
            @endif
        </div>

        <div class="flex-1 p-5 space-y-1.5">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 block mb-2">02 // TRANSACTION TIMELINES</span>
            <p><strong class="text-slate-700 dark:text-slate-300">Issue Timestamp:</strong> {{ $invoice->invoice_date->format('Y-m-d') }}</p>
            <p><strong class="text-slate-700 dark:text-slate-300">Maturity / Due:</strong> {{ $invoice->due_date->format('Y-m-d') }}</p>
            <p class="text-slate-500"><strong class="text-slate-700 dark:text-slate-300">Engine Model:</strong> Clean-Grid v1.0</p>
        </div>
    </div>

    <!-- Grid Data Table -->
    <div class="overflow-x-auto mb-6">
        <table class="w-full text-left border-2 border-slate-900 dark:border-slate-300 font-mono text-xs">
            <thead class="bg-slate-900 text-white dark:bg-slate-800 text-[11px] font-bold uppercase tracking-wider">
                <tr>
                    <th class="p-3 w-12 text-center border-r border-slate-700">#</th>
                    <th class="p-3 border-r border-slate-700">Item Description</th>
                    <th class="p-3 text-right border-r border-slate-700 w-24">Units</th>
                    <th class="p-3 text-right border-r border-slate-700 w-32">Unit Rate</th>
                    <th class="p-3 text-right w-36">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-300 dark:divide-slate-700">
                @foreach($invoice->items as $index => $item)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                    <td class="p-3 text-center border-r border-slate-300 dark:border-slate-700 text-slate-500">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="p-3 border-r border-slate-300 dark:border-slate-700 font-sans font-medium text-slate-900 dark:text-white">{{ $item->description }}</td>
                    <td class="p-3 text-right border-r border-slate-300 dark:border-slate-700">{{ number_format($item->quantity, 2) }}</td>
                    <td class="p-3 text-right border-r border-slate-300 dark:border-slate-700">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="p-3 text-right font-bold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Bottom Wrap: Remittance + Totals -->
    <div class="flex flex-col sm:flex-row gap-6 items-stretch font-mono text-xs">
        <div class="flex-1 border border-slate-300 dark:border-slate-700 p-5 bg-slate-50 dark:bg-slate-800/50">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 block mb-2">03 // REMITTANCE DETAILS</span>
            @if($invoice->payment_instructions)
                <p class="text-slate-700 dark:text-slate-300 mb-2">{{ $invoice->payment_instructions }}</p>
            @endif
            @if($invoice->notes)
                <p class="text-slate-500">{{ $invoice->notes }}</p>
            @endif
        </div>

        <div class="w-full sm:w-80 border-2 border-slate-900 dark:border-slate-300">
            <div class="flex justify-between p-2.5 border-b border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400">
                <span>Subtotal:</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="flex justify-between p-2.5 border-b border-slate-300 dark:border-slate-700 text-rose-600 dark:text-rose-400">
                <span>Discount ({{ $invoice->discount_rate }}%):</span>
                <span>-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="flex justify-between p-2.5 border-b border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400">
                <span>Tax ({{ $invoice->tax_rate }}%):</span>
                <span>+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            @if(!empty($invoice->additional_charges))
                @foreach($invoice->additional_charges as $charge)
                <div class="flex justify-between p-2.5 border-b border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-400">
                    <span>{{ $charge['name'] }} ({{ $charge['type'] === 'percentage' ? $charge['value'].'%' : 'Fixed' }}):</span>
                    <span>+{{ $invoice->currency }} {{ number_format($charge['amount'], 2) }}</span>
                </div>
                @endforeach
            @endif
            <div class="flex justify-between p-3.5 bg-slate-900 text-white dark:bg-slate-800 font-bold text-sm">
                <span>TOTAL DUE:</span>
                <span>{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endif
