@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { color: #1e293b; background: #ffffff; font-size: 13px; line-height: 1.5; padding: 35px 40px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-draft { background: #f1f5f9; color: #475569; }
        .badge-sent { background: #e0e7ff; color: #4338ca; }
        .badge-overdue { background: #ffe4e6; color: #be123c; }
    </style>
</head>
<body>
    {{-- Header: Logo & Brand on Left, Invoice & Status on Right --}}
    <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #0f172a; padding-bottom: 18px; margin-bottom: 28px;">
        <tr>
            <td style="vertical-align: top; width: 60%;">
                @if($invoice->logo)
                    <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 48px; max-width: 180px; margin-bottom: 8px; display: block;">
                @endif
                <div style="font-size: 24px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;">{{ $invoice->user->name }}</div>
                <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ $invoice->user->email }}</div>
            </td>
            <td style="vertical-align: top; width: 40%; text-align: right;">
                <div style="font-size: 28px; font-weight: 300; color: #64748b; letter-spacing: 1px;">INVOICE</div>
                <div style="margin-top: 4px;">
                    <span class="badge badge-{{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
                </div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 6px;">#{{ $invoice->invoice_number }}</div>
            </td>
        </tr>
    </table>

    {{-- Meta Grid: Client Details on Left, Invoice Details on Right --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px;">Billed To</div>
                <div style="font-size: 15px; font-weight: 700; color: #0f172a;">{{ $invoice->client->name }}</div>
                @if($invoice->client->company_name)
                    <div style="color: #334155; font-weight: 600; font-size: 12px; margin-top: 2px;">{{ $invoice->client->company_name }}</div>
                @endif
                @if($invoice->client->address)
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ $invoice->client->address }}</div>
                @endif
                @if($invoice->client->city || $invoice->client->country)
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ $invoice->client->city }}{{ $invoice->client->city && $invoice->client->country ? ', ' : '' }}{{ $invoice->client->country }}</div>
                @endif
                @if($invoice->client->email)
                    <div style="color: #475569; font-size: 12px; margin-top: 2px;">{{ $invoice->client->email }}</div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right;">
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px;">Invoice Details</div>
                <div style="color: #475569; font-size: 12px; margin-top: 3px;"><strong style="color: #0f172a;">Issue Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</div>
                <div style="color: #475569; font-size: 12px; margin-top: 3px;"><strong style="color: #0f172a;">Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</div>
                <div style="color: #475569; font-size: 12px; margin-top: 3px;"><strong style="color: #0f172a;">Currency:</strong> {{ strtoupper($invoice->currency) }}</div>
            </td>
        </tr>
    </table>

    {{-- Line Items Table --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
        <thead>
            <tr>
                <th style="width: 50%; text-align: left; padding: 10px 8px; border-bottom: 2px solid #0f172a; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0f172a;">Description</th>
                <th style="width: 12%; text-align: right; padding: 10px 8px; border-bottom: 2px solid #0f172a; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0f172a;">Qty</th>
                <th style="width: 18%; text-align: right; padding: 10px 8px; border-bottom: 2px solid #0f172a; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0f172a;">Unit Price</th>
                <th style="width: 20%; text-align: right; padding: 10px 8px; border-bottom: 2px solid #0f172a; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0f172a;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td style="padding: 11px 8px; border-bottom: 1px solid #e2e8f0; font-size: 12px; color: #0f172a; font-weight: 600;">{{ $item->description }}</td>
                <td style="padding: 11px 8px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-align: right; color: #334155;">{{ number_format($item->quantity, 2) }}</td>
                <td style="padding: 11px 8px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-align: right; color: #334155;">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                <td style="padding: 11px 8px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-align: right; font-weight: 700; color: #0f172a;">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Bottom Section: Notes on Left, Totals on Right --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
        <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 25px;">
                @if($invoice->payment_instructions || $invoice->notes)
                    <div style="border-top: 1px solid #e2e8f0; padding-top: 15px;">
                        @if($invoice->payment_instructions)
                            <div style="font-size: 11px; color: #475569; margin-bottom: 8px;">
                                <strong style="color: #0f172a;">Payment Instructions:</strong><br>{{ $invoice->payment_instructions }}
                            </div>
                        @endif
                        @if($invoice->notes)
                            <div style="font-size: 11px; color: #475569;">
                                <strong style="color: #0f172a;">Notes:</strong><br>{{ $invoice->notes }}
                            </div>
                        @endif
                    </div>
                @endif
            </td>
            <td style="width: 45%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="text-align: right; padding: 5px 8px; font-size: 12px; color: #64748b;">Subtotal:</td>
                        <td style="text-align: right; padding: 5px 8px; font-size: 12px; font-weight: 600; color: #0f172a; width: 130px;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td style="text-align: right; padding: 5px 8px; font-size: 12px; color: #64748b;">Discount ({{ $invoice->discount_rate }}%):</td>
                        <td style="text-align: right; padding: 5px 8px; font-size: 12px; font-weight: 600; color: #e11d48;">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->tax_amount > 0)
                    <tr>
                        <td style="text-align: right; padding: 5px 8px; font-size: 12px; color: #64748b;">Tax ({{ $invoice->tax_rate }}%):</td>
                        <td style="text-align: right; padding: 5px 8px; font-size: 12px; font-weight: 600; color: #0f172a;">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if(!empty($invoice->additional_charges))
                        @foreach($invoice->additional_charges as $charge)
                        <tr>
                            <td style="text-align: right; padding: 5px 8px; font-size: 12px; color: #64748b;">{{ $charge['name'] }} ({{ $charge['type'] === 'percentage' ? $charge['value'].'%' : 'Fixed' }}):</td>
                            <td style="text-align: right; padding: 5px 8px; font-size: 12px; font-weight: 600; color: #0f172a;">+{{ $invoice->currency }} {{ number_format($charge['amount'], 2) }}</td>
                        </tr>
                        @endforeach
                    @endif
                    <tr style="border-top: 2px solid #0f172a;">
                        <td style="text-align: right; padding: 10px 8px 5px; font-size: 14px; font-weight: 800; color: #0f172a;">Grand Total:</td>
                        <td style="text-align: right; padding: 10px 8px 5px; font-size: 16px; font-weight: 800; color: #0f172a;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
@else
{{-- Web View: Modern Minimalist Style --}}
<div class="p-4 sm:p-8 md:p-12 text-slate-900 dark:text-slate-100">
    <div class="flex flex-col sm:flex-row sm:items-start justify-between border-b-2 border-slate-900 dark:border-slate-100 pb-6 mb-8 gap-4">
        <div>
            @if($invoice->logo)
                <img src="{{ $invoice->logo->url }}" alt="Logo" class="max-h-12 max-w-[180px] object-contain mb-2">
            @endif
            <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ $invoice->user->name }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $invoice->user->email }}</p>
        </div>
        <div class="sm:text-right">
            <span class="text-3xl font-light text-slate-400 dark:text-slate-500 uppercase tracking-wider block">INVOICE</span>
            <div class="mt-2 flex items-center sm:justify-end gap-2">
                <span class="font-mono text-sm font-bold text-slate-900 dark:text-white">#{{ $invoice->invoice_number }}</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                    @if($invoice->status === 'paid') bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400
                    @elseif($invoice->status === 'sent') bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-400
                    @elseif($invoice->status === 'overdue') bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-400
                    @else bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300 @endif">
                    {{ $invoice->status }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 mb-10 text-sm">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">Billed To</p>
            <p class="text-base font-bold text-slate-900 dark:text-white">{{ $invoice->client->name }}</p>
            @if($invoice->client->company_name)
                <p class="font-medium text-slate-700 dark:text-slate-300 text-xs mt-0.5">{{ $invoice->client->company_name }}</p>
            @endif
            @if($invoice->client->address)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $invoice->client->address }}</p>
            @endif
            @if($invoice->client->city || $invoice->client->country)
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->client->city }}{{ $invoice->client->city && $invoice->client->country ? ', ' : '' }}{{ $invoice->client->country }}</p>
            @endif
            @if($invoice->client->email)
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">{{ $invoice->client->email }}</p>
            @endif
        </div>

        <div class="sm:text-right space-y-1.5">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">Invoice Details</p>
            <p class="text-xs text-slate-600 dark:text-slate-400"><span class="font-semibold text-slate-800 dark:text-slate-200">Issue Date:</span> {{ $invoice->invoice_date->format('M d, Y') }}</p>
            <p class="text-xs text-slate-600 dark:text-slate-400"><span class="font-semibold text-slate-800 dark:text-slate-200">Due Date:</span> {{ $invoice->due_date->format('M d, Y') }}</p>
            <p class="text-xs text-slate-600 dark:text-slate-400"><span class="font-semibold text-slate-800 dark:text-slate-200">Currency:</span> {{ strtoupper($invoice->currency) }}</p>
        </div>
    </div>

    <div class="overflow-x-auto mb-10">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-slate-900 dark:border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-900 dark:text-white">
                    <th class="py-3 px-2">Description</th>
                    <th class="py-3 px-2 text-right">Qty</th>
                    <th class="py-3 px-2 text-right">Unit Price</th>
                    <th class="py-3 px-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                @foreach($invoice->items as $item)
                <tr>
                    <td class="py-4 px-2 font-medium text-slate-800 dark:text-slate-200">{{ $item->description }}</td>
                    <td class="py-4 px-2 text-right font-mono text-xs text-slate-600 dark:text-slate-400">{{ number_format($item->quantity, 2) }}</td>
                    <td class="py-4 px-2 text-right font-mono text-xs text-slate-600 dark:text-slate-400">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="py-4 px-2 text-right font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-end mb-10">
        <div class="w-full sm:w-72 space-y-2 text-sm">
            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>Subtotal:</span>
                <span class="font-mono font-medium">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="flex justify-between text-rose-600 dark:text-rose-400">
                <span>Discount ({{ $invoice->discount_rate }}%):</span>
                <span class="font-mono font-medium">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($invoice->tax_amount > 0)
            <div class="flex justify-between text-slate-600 dark:text-slate-400">
                <span>Tax ({{ $invoice->tax_rate }}%):</span>
                <span class="font-mono font-medium">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            @if(!empty($invoice->additional_charges))
                @foreach($invoice->additional_charges as $charge)
                <div class="flex justify-between text-slate-600 dark:text-slate-400">
                    <span>{{ $charge['name'] }} ({{ $charge['type'] === 'percentage' ? $charge['value'].'%' : 'Fixed' }}):</span>
                    <span class="font-mono font-medium">+{{ $invoice->currency }} {{ number_format($charge['amount'], 2) }}</span>
                </div>
                @endforeach
            @endif
            <div class="pt-3 border-t-2 border-slate-900 dark:border-white flex justify-between items-baseline font-black text-base text-slate-900 dark:text-white">
                <span>Grand Total:</span>
                <span class="font-mono text-lg">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
    </div>

    @if($invoice->notes || $invoice->payment_instructions)
    <div class="pt-6 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 space-y-2">
        @if($invoice->payment_instructions)
            <p><strong class="text-slate-900 dark:text-white">Payment Instructions:</strong> {{ $invoice->payment_instructions }}</p>
        @endif
        @if($invoice->notes)
            <p><strong class="text-slate-900 dark:text-white">Notes:</strong> {{ $invoice->notes }}</p>
        @endif
    </div>
    @endif
</div>
@endif
