@if($isPdf ?? false)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Arial, sans-serif; }
        body { color: #1e293b; background: #ffffff; font-size: 12px; line-height: 1.4; }
        .split-table { width: 100%; border-collapse: collapse; }
        .sidebar { width: 32%; background: #1e1b4b; color: #ffffff; padding: 30px 22px; vertical-align: top; }
        .main-content { width: 68%; background: #ffffff; padding: 30px 26px; vertical-align: top; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: 800; text-transform: uppercase; background: #8b5cf6; color: #ffffff; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .item-table th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 8px 6px; text-align: left; font-size: 9px; font-weight: 800; text-transform: uppercase; color: #475569; }
        .item-table td { padding: 9px 6px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
    </style>
</head>
<body>
    <table class="split-table">
        <tr>
            <!-- Left Sidebar -->
            <td class="sidebar">
                <!-- Dedicated Logo Frame -->
                <div style="height: 60px; max-height: 60px; width: 100%; overflow: hidden; margin-bottom: 16px;">
                    @if($invoice->logo)
                        <img src="{{ $invoice->logo->absolutePath }}" alt="Logo" style="max-height: 55px; max-width: 170px; object-fit: contain; display: block;">
                    @else
                        <div style="font-size: 18px; font-weight: 900; color: #8b5cf6; letter-spacing: -0.5px;">{{ strtoupper(substr($invoice->user->company_name ?? $invoice->user->name, 0, 16)) }}</div>
                        <div style="font-size: 9px; opacity: 0.7; text-transform: uppercase; letter-spacing: 1px;">Verified Issuer</div>
                    @endif
                </div>

                <div style="font-size: 13px; font-weight: 800; margin-bottom: 4px;">{{ $invoice->user->name }}</div>
                <div style="font-size: 10px; opacity: 0.8; margin-bottom: 15px;">{{ $invoice->user->email }}</div>

                <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.15); margin: 15px 0;">

                <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; margin-bottom: 6px;">Billed Client</div>
                <div style="font-size: 12px; font-weight: 700;">{{ $invoice->client->name }}</div>
                @if($invoice->client->company_name)
                    <div style="font-size: 10px; opacity: 0.9;">{{ $invoice->client->company_name }}</div>
                @endif
                @if($invoice->client->address)
                    <div style="font-size: 10px; opacity: 0.8; margin-top: 2px;">{{ $invoice->client->address }}</div>
                @endif
                @if($invoice->client->city || $invoice->client->country)
                    <div style="font-size: 10px; opacity: 0.8;">{{ $invoice->client->city }}{{ $invoice->client->city && $invoice->client->country ? ', ' : '' }}{{ $invoice->client->country }}</div>
                @endif

                <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.15); margin: 18px 0;">

                @if($invoice->payment_instructions)
                    <div style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.7; margin-bottom: 4px;">Payment Terms</div>
                    <div style="font-size: 9px; opacity: 0.85; line-height: 1.4;">{{ $invoice->payment_instructions }}</div>
                @endif
            </td>

            <!-- Right Main Area -->
            <td class="main-content">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <tr>
                        <td style="vertical-align: top;">
                            <div style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">INVOICE</div>
                            <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase;">Horizon Split-Panel Ledger</div>
                        </td>
                        <td style="vertical-align: top; text-align: right;">
                            <span class="badge">{{ strtoupper($invoice->status) }}</span>
                            <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin-top: 5px;">#{{ $invoice->invoice_number }}</div>
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Date: {{ $invoice->invoice_date->format('M d, Y') }}</div>
                            <div style="font-size: 10px; color: #64748b;">Due: {{ $invoice->due_date->format('M d, Y') }}</div>
                        </td>
                    </tr>
                </table>

                <!-- Line Items Table -->
                <table class="item-table">
                    <thead>
                        <tr>
                            <th style="width: 52%;">Description</th>
                            <th style="width: 14%; text-align: center;">Qty</th>
                            <th style="width: 17%; text-align: right;">Rate</th>
                            <th style="width: 17%; text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td style="font-weight: 600; color: #0f172a;">{{ $item->description }}</td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                            <td style="text-align: right; font-weight: 700;">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Calculations -->
                <table style="width: 100%; border-collapse: collapse; margin-top: 18px;">
                    <tr>
                        <td style="width: 50%; vertical-align: bottom;">
                            <div style="font-size: 9px; color: #94a3b8; font-weight: 700;">CERTIFIED DIGITAL RECORD</div>
                            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Thank you for your valued partnership.</div>
                        </td>
                        <td style="width: 50%; vertical-align: top;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 3px 0; font-size: 11px; color: #64748b; text-align: right;">Subtotal:</td>
                                    <td style="padding: 3px 0; font-size: 11px; font-weight: 600; text-align: right; width: 40%;">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td style="padding: 3px 0; font-size: 11px; color: #16a34a; text-align: right;">Discount:</td>
                                    <td style="padding: 3px 0; font-size: 11px; font-weight: 600; color: #16a34a; text-align: right;">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td style="padding: 3px 0; font-size: 11px; color: #64748b; text-align: right;">Tax:</td>
                                    <td style="padding: 3px 0; font-size: 11px; font-weight: 600; text-align: right;">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr style="border-top: 2px solid #e2e8f0;">
                                    <td style="padding: 8px 0 0; font-size: 13px; font-weight: 900; color: #0f172a; text-align: right;">Total Due:</td>
                                    <td style="padding: 8px 0 0; font-size: 15px; font-weight: 900; color: #8b5cf6; text-align: right;">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
@else
{{-- Web View: Split Sidebar Architecture --}}
<div class="flex flex-col md:flex-row min-h-[640px] bg-white dark:bg-slate-900 rounded-3xl overflow-hidden shadow-sm">
    <!-- Left Sidebar Panel -->
    <div class="md:w-80 shrink-0 bg-slate-900 text-white p-6 sm:p-8 flex flex-col justify-between border-r border-slate-800 space-y-6">
        <div>
            <!-- Dedicated Logo Frame -->
            <div class="invoice-logo-frame h-16 w-48 max-w-full flex items-center justify-start overflow-hidden mb-6">
                @if($invoice->logo)
                    <img src="{{ $invoice->logo->url }}" alt="Company Logo" class="max-h-16 max-w-full object-contain">
                @elseif(!empty($invoice->user->logo_url))
                    <img src="{{ $invoice->user->logo_url }}" alt="Company Logo" class="max-h-16 max-w-full object-contain">
                @else
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center font-black text-lg text-white shadow-md">
                            {{ strtoupper(substr($invoice->user->company_name ?? $invoice->user->name ?? 'IH', 0, 2)) }}
                        </div>
                        <div>
                            <span class="font-black text-xs text-white block">{{ $invoice->user->company_name ?? $invoice->user->name }}</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Verified Merchant</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Issuer Details -->
            <div class="space-y-1 text-xs text-slate-300">
                <p class="font-bold text-white text-sm">{{ $invoice->user->name }}</p>
                <p class="flex items-center gap-2"><i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i> {{ $invoice->user->email }}</p>
            </div>

            <!-- Divider -->
            <div class="my-6 border-t border-slate-800"></div>

            <!-- Client Card -->
            <div class="space-y-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Billed Recipient</span>
                <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 space-y-1">
                    <p class="font-bold text-sm text-white">{{ $invoice->client->name }}</p>
                    @if($invoice->client->company_name)
                        <p class="text-xs text-slate-300 font-medium">{{ $invoice->client->company_name }}</p>
                    @endif
                    @if($invoice->client->address)
                        <p class="text-xs text-slate-400">{{ $invoice->client->address }}</p>
                    @endif
                    @if($invoice->client->city || $invoice->client->country)
                        <p class="text-xs text-slate-400">{{ $invoice->client->city }}{{ $invoice->client->city && $invoice->client->country ? ', ' : '' }}{{ $invoice->client->country }}</p>
                    @endif
                </div>
            </div>

            <!-- QR Verification Box -->
            <div class="mt-6 flex justify-center">
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

        <!-- Signature Authorization -->
        <div class="pt-4 border-t border-slate-800 flex justify-center">
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
    </div>

    <!-- Right Main Column -->
    <div class="flex-1 p-6 sm:p-10 flex flex-col justify-between space-y-6">
        <div>
            <!-- Top Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
                <div>
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">INVOICE</h1>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Horizon Split-Panel Ledger</p>
                </div>
                <div class="sm:text-right flex flex-col sm:items-end gap-1.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                        {{ $invoice->status }}
                    </span>
                    <span class="font-mono text-sm font-bold text-slate-900 dark:text-white mt-1">#{{ $invoice->invoice_number }}</span>
                    <div class="text-xs text-slate-400 flex items-center gap-3 mt-1">
                        <span>Issued: <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->invoice_date->format('M d, Y') }}</strong></span>
                        <span>•</span>
                        <span>Due: <strong class="text-slate-700 dark:text-slate-300">{{ $invoice->due_date->format('M d, Y') }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Item & Description</th>
                            <th class="py-3 px-3 text-center">Qty</th>
                            <th class="py-3 px-4 text-right">Price</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($invoice->items as $item)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-semibold text-slate-900 dark:text-white">{{ $item->description }}</td>
                            <td class="py-3.5 px-3 text-center text-slate-600 dark:text-slate-300">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 font-bold text-[11px]">{{ $item->quantity }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-right text-slate-600 dark:text-slate-300">{{ $invoice->currency }} {{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-900 dark:text-white">{{ $invoice->currency }} {{ number_format($item->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Summary & Stamp -->
        <div class="pt-6 border-t border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="inline-block transform -rotate-12 select-none opacity-85 hover:opacity-100 transition-opacity">
    <svg class="w-24 h-24" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="50" cy="50" r="46" stroke="#8b5cf6" stroke-width="2" stroke-dasharray="3 2" />
        <circle cx="50" cy="50" r="40" stroke="#8b5cf6" stroke-width="1.5" />
        <circle cx="50" cy="50" r="16" fill="#8b5cf6" fill-opacity="0.08" stroke="#8b5cf6" stroke-width="1" />
        <text x="50" y="44" font-size="6.5" font-weight="900" text-anchor="middle" fill="#8b5cf6" letter-spacing="1">OFFICIAL INVOICE</text>
        <text x="50" y="54" font-size="8" font-weight="900" text-anchor="middle" fill="#8b5cf6" letter-spacing="0.5">VERIFIED</text>
        <text x="50" y="63" font-size="5.5" font-weight="800" text-anchor="middle" fill="#8b5cf6">★ AUTHENTIC ★</text>
    </svg>
</div>
                <div>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Guaranteed Merchant Transaction</p>
                    <p class="text-[11px] text-slate-400">All digital deliverables certified via InvoiceHub Ledger.</p>
                </div>
            </div>

            <div class="w-full sm:w-72 bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/80 space-y-2">
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Subtotal:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-xs text-emerald-600">
                    <span>Discount:</span>
                    <span class="font-semibold">-{{ $invoice->currency }} {{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-xs text-slate-500">
                    <span>Tax:</span>
                    <span class="font-semibold text-slate-800 dark:text-slate-200">+{{ $invoice->currency }} {{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex justify-between items-baseline">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white">Total:</span>
                    <span class="text-lg font-black text-blue-600 dark:text-blue-400">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif