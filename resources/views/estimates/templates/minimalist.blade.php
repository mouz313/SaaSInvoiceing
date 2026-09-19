@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Estimate {{ $estimate->estimate_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { color: #1e293b; background: #ffffff; font-size: 13px; line-height: 1.5; padding: 35px 40px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-accepted { background: #dcfce7; color: #15803d; }
        .badge-draft { background: #f1f5f9; color: #475569; }
        .badge-sent { background: #e0e7ff; color: #4338ca; }
        .badge-declined { background: #ffe4e6; color: #be123c; }
        .badge-invoiced { background: #f3e8ff; color: #7e22ce; }
    </style>
</head>
<body>
    {{-- Header: Logo & Brand on Left, Estimate & Status on Right --}}
    <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #0f172a; padding-bottom: 18px; margin-bottom: 28px;">
        <tr>
            <td style="vertical-align: top; width: 60%;">
                @if($estimate->logo)
                    <img src="{{ $estimate->logo->absolutePath }}" alt="Logo" style="max-height: 48px; max-width: 180px; margin-bottom: 8px; display: block;">
                @endif
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">{{ $estimate->user->company_name ?: $estimate->user->name }}</div>
                <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ $estimate->user->email }}</div>
            </td>
            <td style="vertical-align: top; width: 40%; text-align: right;">
                <div style="font-size: 28px; font-weight: 300; color: #64748b; letter-spacing: 1px;">QUOTATION</div>
                <div style="margin-top: 4px;">
                    <span class="badge badge-{{ $estimate->status }}">{{ strtoupper($estimate->status) }}</span>
                </div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 6px;">#{{ $estimate->estimate_number }}</div>
            </td>
        </tr>
    </table>

    {{-- Meta Grid: Client Details on Left, Estimate Details on Right --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px;">Prepared For</div>
                <div style="font-size: 15px; font-weight: 700; color: #0f172a;">{{ $estimate->client->name }}</div>
                @if($estimate->client->company_name)
                    <div style="color: #334155; font-weight: 600; font-size: 12px; margin-top: 2px;">{{ $estimate->client->company_name }}</div>
                @endif
                @if($estimate->client->address)
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ $estimate->client->address }}</div>
                @endif
                @if($estimate->client->city || $estimate->client->country)
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ $estimate->client->city }}{{ $estimate->client->city && $estimate->client->country ? ', ' : '' }}{{ $estimate->client->country }}</div>
                @endif
                @if($estimate->client->email)
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ $estimate->client->email }}</div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px;">Proposal Timeline</div>
                <div style="color: #475569; font-size: 12px; margin-top: 3px;"><strong style="color: #0f172a;">Quote Date:</strong> {{ $estimate->estimate_date->format('M d, Y') }}</div>
                <div style="color: #475569; font-size: 12px; margin-top: 3px;"><strong style="color: #0f172a;">Valid Until:</strong> {{ $estimate->expiry_date->format('M d, Y') }}</div>
                @if($estimate->convertedInvoice)
                    <div style="color: #7e22ce; font-size: 12px; margin-top: 3px; font-weight: 700;">Invoiced as: #{{ $estimate->convertedInvoice->invoice_number }}</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Line Items Table --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px;">
        <thead>
            <tr style="border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 10px 0; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b;">Description</th>
                <th style="padding: 10px 0; text-align: center; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b; width: 60px;">Qty</th>
                <th style="padding: 10px 0; text-align: right; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b; width: 90px;">Rate</th>
                <th style="padding: 10px 0; text-align: right; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b; width: 100px;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($estimate->items as $item)
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 12px 0; font-weight: 500; color: #0f172a;">{{ $item->description }}</td>
                    <td style="padding: 12px 0; text-align: center; color: #475569;">{{ (float) $item->quantity }}</td>
                    <td style="padding: 12px 0; text-align: right; color: #475569;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="padding: 12px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals Breakdown on Right --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 20px;">
                @if($estimate->notes)
                    <div style="margin-bottom: 15px;">
                        <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 4px;">Notes</div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.6;">{{ $estimate->notes }}</div>
                    </div>
                @endif
                @if($estimate->terms)
                    <div>
                        <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 4px;">Terms &amp; Conditions</div>
                        <div style="font-size: 11px; color: #64748b; line-height: 1.5;">{{ $estimate->terms }}</div>
                    </div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; color: #64748b;">Subtotal:</td>
                        <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #0f172a;">{{ $estimate->currency }} {{ number_format($estimate->subtotal, 2) }}</td>
                    </tr>
                    @if($estimate->discount_amount > 0)
                        <tr>
                            <td style="padding: 4px 0; color: #e11d48;">Discount ({{ (float) $estimate->discount_rate }}%):</td>
                            <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #e11d48;">-{{ $estimate->currency }} {{ number_format($estimate->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($estimate->tax_amount > 0)
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">Tax ({{ (float) $estimate->tax_rate }}%):</td>
                            <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #0f172a;">+{{ $estimate->currency }} {{ number_format($estimate->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if(!empty($estimate->additional_charges))
                        @foreach($estimate->additional_charges as $charge)
                            <tr>
                                <td style="padding: 4px 0; color: #64748b;">{{ $charge['name'] ?? 'Extra Charge' }}{{ ($charge['type'] ?? '') === 'percentage' ? ' ('.$charge['value'].'%)' : '' }}:</td>
                                <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #0f172a;">+{{ $estimate->currency }} {{ number_format($charge['amount'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr style="border-top: 2px solid #0f172a;">
                        <td style="padding: 10px 0 0 0; font-size: 15px; font-weight: 800; color: #0f172a;">Quoted Total:</td>
                        <td style="padding: 10px 0 0 0; text-align: right; font-size: 18px; font-weight: 800; color: #0f172a;">{{ $estimate->currency }} {{ number_format($estimate->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
@else
{{-- Web View --}}
<div class="p-6 sm:p-10 text-slate-800 dark:text-slate-200">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start pb-6 border-b-2 border-slate-900 dark:border-slate-700 gap-6">
        <div>
            @if($estimate->logo)
                <img src="{{ $estimate->logo->url }}" alt="Logo" class="max-h-14 max-w-[200px] mb-3 object-contain">
            @endif
            <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $estimate->user->company_name ?: $estimate->user->name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $estimate->user->email }}</p>
        </div>
        <div class="text-left sm:text-right">
            <span class="text-2xl sm:text-3xl font-light text-slate-400 dark:text-slate-500 uppercase tracking-widest block">QUOTATION</span>
            <div class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-{{ $estimate->status_color }}-100 dark:bg-{{ $estimate->status_color }}-950/60 text-{{ $estimate->status_color }}-700 dark:text-{{ $estimate->status_color }}-400">
                    {{ $estimate->status }}
                </span>
            </div>
            <p class="text-sm font-bold text-slate-900 dark:text-white mt-2">#{{ $estimate->estimate_number }}</p>
        </div>
    </div>

    {{-- Meta --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 my-8">
        <div>
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 block mb-2">Prepared For</span>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ $estimate->client->name }}</h2>
            @if($estimate->client->company_name)
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $estimate->client->company_name }}</p>
            @endif
            @if($estimate->client->address)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $estimate->client->address }}</p>
            @endif
            @if($estimate->client->city || $estimate->client->country)
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $estimate->client->city }}{{ $estimate->client->city && $estimate->client->country ? ', ' : '' }}{{ $estimate->client->country }}</p>
            @endif
            @if($estimate->client->email)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $estimate->client->email }}</p>
            @endif
        </div>
        <div class="text-left sm:text-right space-y-1.5">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 block mb-2">Proposal Details</span>
            <p class="text-xs text-slate-500 dark:text-slate-400"><strong class="text-slate-900 dark:text-white">Quote Date:</strong> {{ $estimate->estimate_date->format('M d, Y') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400"><strong class="text-slate-900 dark:text-white">Valid Until:</strong> {{ $estimate->expiry_date->format('M d, Y') }}</p>
            @if($estimate->convertedInvoice)
                <p class="text-xs text-purple-600 dark:text-purple-400 font-bold mt-2">
                    <a href="{{ route('invoices.show', $estimate->convertedInvoice) }}" class="underline">Converted to Invoice #{{ $estimate->convertedInvoice->invoice_number }} &rarr;</a>
                </p>
            @endif
        </div>
    </div>

    {{-- Line Items --}}
    <div class="overflow-x-auto my-6">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
                <tr class="border-b-2 border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase text-[10px] tracking-wider font-bold">
                    <th class="py-3 px-2">Description</th>
                    <th class="py-3 px-2 text-center w-20">Qty</th>
                    <th class="py-3 px-2 text-right w-28">Rate</th>
                    <th class="py-3 px-2 text-right w-32">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($estimate->items as $item)
                    <tr>
                        <td class="py-3.5 px-2 font-medium text-slate-900 dark:text-white">{{ $item->description }}</td>
                        <td class="py-3.5 px-2 text-center text-slate-600 dark:text-slate-400">{{ (float) $item->quantity }}</td>
                        <td class="py-3.5 px-2 text-right text-slate-600 dark:text-slate-400">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-3.5 px-2 text-right font-bold text-slate-900 dark:text-white">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals Breakdown --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-slate-200 dark:border-slate-800">
        <div class="space-y-4">
            @if($estimate->notes)
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1">Notes</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 whitespace-pre-line">{{ $estimate->notes }}</p>
                </div>
            @endif
            @if($estimate->terms)
                <div>
                    <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1">Terms &amp; Conditions</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 whitespace-pre-line">{{ $estimate->terms }}</p>
                </div>
            @endif
        </div>
        <div class="space-y-2 text-xs sm:text-sm">
            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>Subtotal</span>
                <span class="font-bold text-slate-900 dark:text-white">{{ $estimate->currency }} {{ number_format($estimate->subtotal, 2) }}</span>
            </div>
            @if($estimate->discount_amount > 0)
                <div class="flex justify-between text-rose-600">
                    <span>Discount ({{ (float) $estimate->discount_rate }}%)</span>
                    <span class="font-bold">-{{ $estimate->currency }} {{ number_format($estimate->discount_amount, 2) }}</span>
                </div>
            @endif
            @if($estimate->tax_amount > 0)
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>Tax ({{ (float) $estimate->tax_rate }}%)</span>
                    <span class="font-bold text-slate-900 dark:text-white">+{{ $estimate->currency }} {{ number_format($estimate->tax_amount, 2) }}</span>
                </div>
            @endif
            @if(!empty($estimate->additional_charges))
                @foreach($estimate->additional_charges as $charge)
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>{{ $charge['name'] ?? 'Extra Charge' }}{{ ($charge['type'] ?? '') === 'percentage' ? ' ('.$charge['value'].'%)' : '' }}</span>
                        <span class="font-bold text-slate-900 dark:text-white">+{{ $estimate->currency }} {{ number_format($charge['amount'] ?? 0, 2) }}</span>
                    </div>
                @endforeach
            @endif
            <div class="flex justify-between pt-3 border-t-2 border-slate-900 dark:border-slate-700 text-base sm:text-lg font-black text-slate-900 dark:text-white">
                <span>Quoted Total</span>
                <span class="text-indigo-600 dark:text-indigo-400">{{ $estimate->currency }} {{ number_format($estimate->total, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endif
