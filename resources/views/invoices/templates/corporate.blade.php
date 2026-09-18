@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Georgia', Times, serif; }
        body { color: #1e293b; background: #ffffff; font-size: 13px; line-height: 1.5; padding: 0; }
        a { color: #1e293b; text-decoration: none; }
        .top-bar { background: #0f172a; color: #ffffff; padding: 25px 35px; }
        .content { padding: 30px 35px; }
        .meta-heading { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #0f172a; border-bottom: 1px solid #0f172a; padding-bottom: 3px; margin-bottom: 8px; }
        .meta-heading-right { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #0f172a; border-bottom: 1px solid #0f172a; padding-bottom: 3px; margin-bottom: 8px; text-align: right; }
        table.corp-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; font-family: Helvetica, Arial, sans-serif; }
        table.corp-table th { background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; padding: 9px 10px; font-size: 11px; font-weight: bold; text-transform: uppercase; text-align: left; }
        table.corp-table td { border: 1px solid #e2e8f0; padding: 9px 10px; font-size: 12px; }
        .grand-total-row td { background: #0f172a; color: #ffffff; font-weight: bold; font-size: 13px; padding: 9px 10px; text-align: right; }
        .sig-line { width: 180px; border-top: 1px solid #94a3b8; margin-top: 35px; text-align: center; padding-top: 5px; font-size: 11px; }
    </style>
</head>
<body>
    {{-- Header Banner --}}
    <div class="top-bar">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: middle;">
                    @if($invoice->logo)
                        <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 44px; max-width: 160px; margin-bottom: 6px; display: block;">
                    @endif
                    <div style="font-size: 24px; font-weight: bold; letter-spacing: 0.5px; color: #ffffff;">{{ $invoice->user->name }}</div>
                    <div style="font-size: 12px; color: #cbd5e1; margin-top: 2px;">{{ $invoice->user->email }}</div>
                </td>
                <td style="vertical-align: middle; text-align: right;">
                    <div style="font-size: 20px; letter-spacing: 2px; color: #ffffff;">OFFICIAL INVOICE</div>
                    <div style="font-size: 13px; color: #94a3b8; margin-top: 4px; font-family: Helvetica, Arial, sans-serif;">NO. {{ $invoice->invoice_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        {{-- Metadata Row --}}
        <table style="width: 100%; border-collapse: collapse; border-bottom: 1px solid #cbd5e1; padding-bottom: 20px; margin-bottom: 25px;">
            <tr>
                <td style="width: 55%; vertical-align: top; font-family: Helvetica, Arial, sans-serif; font-size: 12px;">
                    <div class="meta-heading">Recipient Client Information</div>
                    <div style="font-size: 14px; font-weight: bold; color: #0f172a;">{{ $invoice->client->name }}</div>
                    @if($invoice->client->company_name)
                        <div style="color: #475569; margin-top: 2px;">{{ $invoice->client->company_name }}</div>
                    @endif
                    @if($invoice->client->address)
                        <div style="color: #64748b; margin-top: 2px;">{{ $invoice->client->address }}</div>
                    @endif
                    @if($invoice->client->city || $invoice->client->country)
                        <div style="color: #64748b; margin-top: 2px;">{{ $invoice->client->city }}{{ $invoice->client->city && $invoice->client->country ? ', ' : '' }}{{ $invoice->client->country }}</div>
                    @endif
                    @if($invoice->client->tax_id)
                        <div style="color: #64748b; margin-top: 2px;">Tax Registration / ID: {{ $invoice->client->tax_id }}</div>
                    @endif
                </td>
                <td style="width: 45%; vertical-align: top; font-family: Helvetica, Arial, sans-serif; font-size: 12px; text-align: right;">
                    <div class="meta-heading-right">Billing Details</div>
                    <div style="margin-top: 2px;"><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d F Y') }}</div>
                    <div style="margin-top: 2px;"><strong>Payment Due:</strong> {{ $invoice->due_date->format('d F Y') }}</div>
                    <div style="margin-top: 2px;"><strong>Status:</strong> {{ strtoupper($invoice->status) }}</div>
                    <div style="margin-top: 2px;"><strong>Currency:</strong> {{ strtoupper($invoice->currency) }}</div>
                </td>
            </tr>
        </table>

        {{-- Line Items Table --}}
        <table class="corp-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Item &amp; Description</th>
                    <th style="width: 12%; text-align: right;">Quantity</th>
                    <th style="width: 18%; text-align: right;">Rate</th>
                    <th style="width: 20%; text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Summary Table --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; font-family: Helvetica, Arial, sans-serif;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    @if($invoice->notes || $invoice->payment_instructions)
                    <div style="font-size: 11px; background: #f8fafc; padding: 12px; border-left: 3px solid #0f172a; margin-right: 20px;">
                        @if($invoice->payment_instructions)
                            <p><strong>Remittance &amp; Bank Instructions:</strong> {{ $invoice->payment_instructions }}</p>
                        @endif
                        @if($invoice->notes)
                            <p style="margin-top: 4px;"><strong>Terms &amp; Conditions:</strong> {{ $invoice->notes }}</p>
                        @endif
                    </div>
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="text-align: right; padding: 5px 10px; font-size: 12px; color: #64748b;">Subtotal:</td>
                            <td style="text-align: right; padding: 5px 10px; font-size: 12px; font-weight: 600; width: 140px;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->discount_amount > 0)
                        <tr>
                            <td style="text-align: right; padding: 5px 10px; font-size: 12px; color: #64748b;">Discount ({{ $invoice->discount_rate }}%):</td>
                            <td style="text-align: right; padding: 5px 10px; font-size: 12px; font-weight: 600; color: #e11d48;">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->tax_amount > 0)
                        <tr>
                            <td style="text-align: right; padding: 5px 10px; font-size: 12px; color: #64748b;">Tax ({{ $invoice->tax_rate }}%):</td>
                            <td style="text-align: right; padding: 5px 10px; font-size: 12px; font-weight: 600;">{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if(!empty($invoice->additional_charges))
                            @foreach($invoice->additional_charges as $charge)
                            <tr>
                                <td style="text-align: right; padding: 5px 10px; font-size: 12px; color: #64748b;">{{ $charge['name'] }} ({{ $charge['type'] === 'percentage' ? $charge['value'].'%' : 'Fixed' }}):</td>
                                <td style="text-align: right; padding: 5px 10px; font-size: 12px; font-weight: 600;">+{{ $invoice->currency }} {{ number_format($charge['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        @endif
                        <tr class="grand-total-row">
                            <td style="padding: 8px 10px;">TOTAL PAYABLE:</td>
                            <td style="padding: 8px 10px;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Signatures --}}
        <table style="width: 100%; border-collapse: collapse; margin-top: 35px; border-top: 1px solid #cbd5e1; padding-top: 15px; font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #64748b;">
            <tr>
                <td style="vertical-align: bottom;">Authorized Corporate Representative</td>
                <td style="vertical-align: bottom; text-align: right;">
                    <div style="display: inline-block;" class="sig-line">Official Signature &amp; Date</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
@else
{{-- Web View: Corporate Classic Style --}}
<div>
    <!-- Corporate Header Banner -->
    <div class="bg-slate-900 text-white p-4 sm:p-8 md:p-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            @if($invoice->logo)
                <img src="{{ $invoice->logo->url }}" alt="Logo" class="max-h-10 max-w-[160px] object-contain mb-2">
            @endif
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">{{ $invoice->user->name }}</h1>
            <p class="text-xs text-slate-300 mt-1 font-medium">{{ $invoice->user->email }}</p>
        </div>
        <div class="sm:text-right">
            <span class="text-xs tracking-widest uppercase font-semibold text-blue-400">OFFICIAL INVOICE</span>
            <div class="mt-1 flex items-center sm:justify-end">
                <span class="font-mono text-sm sm:text-base font-bold text-white">#{{ $invoice->invoice_number }}</span>
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                    @if($invoice->status === 'paid') bg-emerald-500 text-white
                    @elseif($invoice->status === 'sent') bg-blue-500 text-white
                    @elseif($invoice->status === 'overdue') bg-rose-500 text-white
                    @else bg-slate-700 text-slate-200 @endif">
                    {{ $invoice->status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Corporate Body Content -->
    <div class="p-4 sm:p-8 md:p-12 text-slate-900 dark:text-slate-100">
        <!-- Metadata Section -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 pb-6 mb-8 border-b border-slate-200 dark:border-slate-800 text-sm">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white border-b-2 border-slate-900 dark:border-slate-300 pb-1.5 mb-3">
                    Recipient Client Information
                </h3>
                <p class="font-bold text-base text-slate-900 dark:text-white">{{ $invoice->client->name }}</p>
                @if($invoice->client->company_name)
                    <p class="text-slate-700 dark:text-slate-300 text-xs font-medium mt-0.5">{{ $invoice->client->company_name }}</p>
                @endif
                @if($invoice->client->address)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $invoice->client->address }}</p>
                @endif
                @if($invoice->client->city || $invoice->client->country)
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $invoice->client->city }}{{ $invoice->client->city && $invoice->client->country ? ', ' : '' }}{{ $invoice->client->country }}</p>
                @endif
                @if($invoice->client->tax_id)
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1"><strong>Tax Registration:</strong> {{ $invoice->client->tax_id }}</p>
                @endif
            </div>

            <div class="sm:text-right space-y-1.5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white border-b-2 border-slate-900 dark:border-slate-300 pb-1.5 mb-3">
                    Billing Details
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-400"><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d F Y') }}</p>
                <p class="text-xs text-slate-600 dark:text-slate-400"><strong>Payment Due:</strong> {{ $invoice->due_date->format('d F Y') }}</p>
                <p class="text-xs text-slate-600 dark:text-slate-400"><strong>Status:</strong> {{ strtoupper($invoice->status) }}</p>
                <p class="text-xs text-slate-600 dark:text-slate-400"><strong>Currency:</strong> {{ strtoupper($invoice->currency) }}</p>
            </div>
        </div>

        <!-- Items Table -->
        <div class="overflow-x-auto mb-8">
            <table class="w-full text-left border border-slate-200 dark:border-slate-700">
                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="p-3.5 border border-slate-200 dark:border-slate-700">Item & Description</th>
                        <th class="p-3.5 border border-slate-200 dark:border-slate-700 text-right">Quantity</th>
                        <th class="p-3.5 border border-slate-200 dark:border-slate-700 text-right">Rate</th>
                        <th class="p-3.5 border border-slate-200 dark:border-slate-700 text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-sm">
                    @foreach($invoice->items as $item)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                        <td class="p-3.5 border border-slate-200 dark:border-slate-700 font-medium text-slate-800 dark:text-slate-200">{{ $item->description }}</td>
                        <td class="p-3.5 border border-slate-200 dark:border-slate-700 text-right font-mono text-xs text-slate-600 dark:text-slate-400">{{ number_format($item->quantity, 2) }}</td>
                        <td class="p-3.5 border border-slate-200 dark:border-slate-700 text-right font-mono text-xs text-slate-600 dark:text-slate-400">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                        <td class="p-3.5 border border-slate-200 dark:border-slate-700 text-right font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Box -->
        <div class="flex justify-end mb-8">
            <div class="w-full sm:w-80 border border-slate-200 dark:border-slate-700 text-sm overflow-hidden rounded-lg shadow-xs">
                <div class="flex justify-between p-2.5 border-b border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-400">
                    <span>Subtotal:</span>
                    <span class="font-mono font-medium">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between p-2.5 border-b border-slate-100 dark:border-slate-800 text-rose-600 dark:text-rose-400">
                    <span>Discount ({{ $invoice->discount_rate }}%):</span>
                    <span class="font-mono font-medium">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between p-2.5 border-b border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-400">
                    <span>Tax ({{ $invoice->tax_rate }}%):</span>
                    <span class="font-mono font-medium">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                @if(!empty($invoice->additional_charges))
                    @foreach($invoice->additional_charges as $charge)
                    <div class="flex justify-between p-2.5 border-b border-slate-100 dark:border-slate-800 text-slate-600 dark:text-slate-400">
                        <span>{{ $charge['name'] }} ({{ $charge['type'] === 'percentage' ? $charge['value'].'%' : 'Fixed' }}):</span>
                        <span class="font-mono font-medium">+{{ $invoice->currency }} {{ number_format($charge['amount'], 2) }}</span>
                    </div>
                    @endforeach
                @endif
                <div class="flex justify-between p-3.5 bg-slate-900 text-white dark:bg-slate-800 font-bold text-sm sm:text-base">
                    <span>TOTAL PAYABLE:</span>
                    <span class="font-mono">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Remittance / Terms -->
        @if($invoice->notes || $invoice->payment_instructions)
        <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-800/40 border-l-4 border-slate-900 dark:border-slate-500 text-xs text-slate-600 dark:text-slate-400 space-y-1.5 mb-10">
            @if($invoice->payment_instructions)
                <p><strong class="text-slate-900 dark:text-white">Remittance & Bank Instructions:</strong> {{ $invoice->payment_instructions }}</p>
            @endif
            @if($invoice->notes)
                <p><strong class="text-slate-900 dark:text-white">Terms & Conditions:</strong> {{ $invoice->notes }}</p>
            @endif
        </div>
        @endif

        <!-- Corporate Signatures -->
        <div class="pt-8 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 text-xs text-slate-500 dark:text-slate-400">
            <div>Authorized Corporate Representative</div>
            <div class="w-48 text-center pt-2 border-t border-slate-400 dark:border-slate-600">Official Signature & Date</div>
        </div>
    </div>
</div>
@endif
