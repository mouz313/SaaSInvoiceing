@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif; }
        body { color: #1e293b; background: #ffffff; font-size: 13px; line-height: 1.5; padding: 30px 35px; }
        a { color: #1e293b; text-decoration: none; }
        .gradient-banner { background: #3b82f6; color: #ffffff; padding: 22px 25px; border-radius: 12px; margin-bottom: 25px; }
        .creator-title { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; color: #ffffff; }
        .badge-pill { display: inline-block; background: rgba(255, 255, 255, 0.25); padding: 3px 10px; border-radius: 9999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #ffffff; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; }
        .card-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #6366f1; margin-bottom: 6px; }
        table.creative-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table.creative-table th { background: #f1f5f9; padding: 10px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #475569; text-align: left; }
        table.creative-table td { padding: 11px 12px; border-bottom: 1px solid #f1f5f9; text-align: left; font-size: 12px; }
        .creative-footer { margin-top: 30px; padding: 14px; border-radius: 10px; background: #eff6ff; border: 1px solid #dbeafe; color: #1e40af; font-size: 12px; }
    </style>
</head>
<body>
    {{-- Banner Table --}}
    <div class="gradient-banner">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: middle;">
                    <span class="badge-pill">Creative Studio Invoice</span>
                    @if($invoice->logo)
                        <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 40px; max-width: 140px; margin-top: 8px; display: block;">
                    @endif
                    <div class="creator-title" style="margin-top: 6px;">{{ $invoice->user->name }}</div>
                    <div style="color: #e0e7ff; font-size: 12px; margin-top: 2px;">{{ $invoice->user->email }}</div>
                </td>
                <td style="vertical-align: middle; text-align: right;">
                    <div style="font-size: 11px; color: #e0e7ff; text-transform: uppercase; font-weight: 700;">Invoice No</div>
                    <div style="font-size: 22px; font-weight: 800; color: #ffffff; margin-top: 2px;">#{{ $invoice->invoice_number }}</div>
                    <div style="margin-top: 6px;">
                        <span class="badge-pill">{{ strtoupper($invoice->status) }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Cards Row --}}
    <table style="width: 100%; border-collapse: separate; border-spacing: 15px 0; margin-left: -15px; margin-right: -15px; margin-bottom: 25px;">
        <tr>
            <td style="width: 50%; vertical-align: top;" class="info-card">
                <div class="card-label">Client Details</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">{{ $invoice->client->name }}</div>
                @if($invoice->client->company_name)
                    <div style="color: #475569; font-weight: 600; font-size: 12px; margin-top: 2px;">{{ $invoice->client->company_name }}</div>
                @endif
                @if($invoice->client->address)
                    <div style="color: #64748b; font-size: 12px; margin-top: 2px;">{{ $invoice->client->address }}</div>
                @endif
                @if($invoice->client->email)
                    <div style="color: #64748b; font-size: 12px; margin-top: 2px;">{{ $invoice->client->email }}</div>
                @endif
            </td>
            <td style="width: 50%; vertical-align: top;" class="info-card">
                <div class="card-label">Timeline &amp; Currency</div>
                <div style="font-size: 12px; margin-top: 2px;"><strong>Issue Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</div>
                <div style="font-size: 12px; margin-top: 4px;"><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</div>
                <div style="font-size: 12px; margin-top: 4px;"><strong>Currency:</strong> {{ strtoupper($invoice->currency) }}</div>
            </td>
        </tr>
    </table>

    {{-- Line Items Table --}}
    <table class="creative-table">
        <thead>
            <tr>
                <th style="width: 50%;">Item / Deliverable</th>
                <th style="width: 12%; text-align: right;">Hours / Qty</th>
                <th style="width: 18%; text-align: right;">Rate</th>
                <th style="width: 20%; text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td><strong>{{ $item->description }}</strong></td>
                <td style="text-align: right;">{{ number_format($item->quantity, 2) }}</td>
                <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right; font-weight: 700; color: #0f172a;">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Bottom Section: Instructions on Left, Totals Card on Right --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                @if($invoice->notes || $invoice->payment_instructions)
                <div class="creative-footer" style="margin-top: 0;">
                    @if($invoice->payment_instructions)
                        <div style="margin-bottom: 4px;"><strong>Payment Note:</strong> {{ $invoice->payment_instructions }}</div>
                    @endif
                    @if($invoice->notes)
                        <div><strong>Special Instructions:</strong> {{ $invoice->notes }}</div>
                    @endif
                </div>
                @endif
            </td>
            <td style="width: 50%; vertical-align: top;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="text-align: left; padding: 4px 0; font-size: 12px; color: #64748b;">Subtotal:</td>
                            <td style="text-align: right; padding: 4px 0; font-size: 12px; font-weight: 600;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        @if($invoice->discount_amount > 0)
                        <tr>
                            <td style="text-align: left; padding: 4px 0; font-size: 12px; color: #e11d48;">Discount ({{ $invoice->discount_rate }}%):</td>
                            <td style="text-align: right; padding: 4px 0; font-size: 12px; font-weight: 600; color: #e11d48;">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->tax_amount > 0)
                        <tr>
                            <td style="text-align: left; padding: 4px 0; font-size: 12px; color: #64748b;">Tax ({{ $invoice->tax_rate }}%):</td>
                            <td style="text-align: right; padding: 4px 0; font-size: 12px; font-weight: 600;">{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if(!empty($invoice->additional_charges))
                            @foreach($invoice->additional_charges as $charge)
                            <tr>
                                <td style="text-align: left; padding: 4px 0; font-size: 12px; color: #64748b;">{{ $charge['name'] }} ({{ $charge['type'] === 'percentage' ? $charge['value'].'%' : 'Fixed' }}):</td>
                                <td style="text-align: right; padding: 4px 0; font-size: 12px; font-weight: 600;">+{{ $invoice->currency }} {{ number_format($charge['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        @endif
                        <tr style="border-top: 2px dashed #cbd5e1;">
                            <td style="text-align: left; padding-top: 8px; font-size: 13px; font-weight: 700; color: #0f172a;">Total Due:</td>
                            <td style="text-align: right; padding-top: 8px; font-size: 18px; font-weight: 800; color: #2563eb;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
@else
{{-- Web View: Creative Bold Style --}}
<div class="p-4 sm:p-8 md:p-12 text-slate-900 dark:text-slate-100">
    <!-- Gradient Header Banner -->
    <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white rounded-2xl p-6 sm:p-8 mb-8 shadow-lg shadow-indigo-500/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="inline-block bg-white/20 backdrop-blur-xs px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider">
                Creative Studio Invoice
            </span>
            @if($invoice->logo)
                <img src="{{ $invoice->logo->url }}" alt="Logo" class="max-h-10 max-w-[140px] object-contain mt-2">
            @endif
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2">{{ $invoice->user->name }}</h1>
            <p class="text-xs text-blue-100 mt-0.5">{{ $invoice->user->email }}</p>
        </div>
        <div class="sm:text-right">
            <span class="text-[11px] uppercase tracking-wider text-blue-100 font-bold block">Invoice No</span>
            <span class="font-mono text-2xl font-black text-white">#{{ $invoice->invoice_number }}</span>
            <div class="mt-2 sm:justify-end flex">
                <span class="inline-block bg-white/25 backdrop-blur-xs px-3 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider text-white">
                    {{ $invoice->status }}
                </span>
            </div>
        </div>
    </div>

    <!-- Cards Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-5 border border-slate-200 dark:border-slate-700/80">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block mb-2">
                Client Details
            </span>
            <p class="font-bold text-slate-900 dark:text-white text-base">{{ $invoice->client->name }}</p>
            @if($invoice->client->company_name)
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 mt-0.5">{{ $invoice->client->company_name }}</p>
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

        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-5 border border-slate-200 dark:border-slate-700/80 space-y-1.5 text-xs">
            <span class="text-[10px] font-extrabold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block mb-2">
                Timeline & Currency
            </span>
            <p class="text-slate-600 dark:text-slate-400"><strong class="text-slate-800 dark:text-slate-200">Issue Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
            <p class="text-slate-600 dark:text-slate-400"><strong class="text-slate-800 dark:text-slate-200">Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
            <p class="text-slate-600 dark:text-slate-400"><strong class="text-slate-800 dark:text-slate-200">Currency:</strong> {{ strtoupper($invoice->currency) }}</p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="overflow-x-auto mb-8">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-wider">
                    <th class="p-3.5 rounded-l-xl">Item / Deliverable</th>
                    <th class="p-3.5 text-right">Hours / Qty</th>
                    <th class="p-3.5 text-right">Rate</th>
                    <th class="p-3.5 rounded-r-xl text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                @foreach($invoice->items as $item)
                <tr>
                    <td class="p-3.5 font-medium text-slate-800 dark:text-slate-200">{{ $item->description }}</td>
                    <td class="p-3.5 text-right font-mono text-xs text-slate-600 dark:text-slate-400">{{ number_format($item->quantity, 2) }}</td>
                    <td class="p-3.5 text-right font-mono text-xs text-slate-600 dark:text-slate-400">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                    <td class="p-3.5 text-right font-mono font-bold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Totals Card -->
    <div class="flex justify-end mb-8">
        <div class="w-full sm:w-80 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-2xl p-5 text-sm space-y-2">
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
            <div class="pt-3 border-t-2 border-dashed border-slate-300 dark:border-slate-700 flex justify-between items-baseline">
                <span class="font-bold text-slate-900 dark:text-white">Total Due</span>
                <span class="font-mono text-2xl font-black bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                    {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Friendly Footer -->
    @if($invoice->notes || $invoice->payment_instructions)
    <div class="rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-100 dark:border-blue-900/60 p-4 text-xs text-blue-900 dark:text-blue-300 space-y-1">
        @if($invoice->payment_instructions)
            <p><strong>Payment Note:</strong> {{ $invoice->payment_instructions }}</p>
        @endif
        @if($invoice->notes)
            <p><strong>Special Instructions:</strong> {{ $invoice->notes }}</p>
        @endif
    </div>
    @endif
</div>
@endif
