<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Statement of Account - {{ $statement['client']->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        body { color: #1e293b; background: #ffffff; font-size: 12px; line-height: 1.5; padding: 35px 40px; }
        .badge { display: inline-block; padding: 2px 7px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .badge-invoice { background: #e0e7ff; color: #4338ca; }
        .badge-payment { background: #dcfce7; color: #15803d; }
    </style>
</head>
<body>
    {{-- Header --}}
    <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 25px;">
        <tr>
            <td style="vertical-align: top; width: 55%;">
                <div style="font-size: 22px; font-weight: 800; color: #0f172a;">{{ $statement['user']->company_name ?: $statement['user']->name }}</div>
                <div style="color: #475569; font-size: 11px; margin-top: 3px;">{{ $statement['user']->email }}</div>
                @if($statement['user']->company_address)
                    <div style="color: #475569; font-size: 11px;">{{ $statement['user']->company_address }}</div>
                @endif
            </td>
            <td style="vertical-align: top; width: 45%; text-align: right;">
                <div style="font-size: 24px; font-weight: 300; color: #64748b; letter-spacing: 1px;">ACCOUNT STATEMENT</div>
                <div style="font-size: 11px; color: #475569; margin-top: 5px;">
                    <strong>Date Generated:</strong> {{ now()->format('M d, Y') }}
                </div>
                <div style="font-size: 11px; color: #475569; margin-top: 2px;">
                    <strong>Statement Period:</strong><br>
                    {{ $statement['startDate'] ? $statement['startDate']->format('M d, Y') : 'Start of Account' }}
                    to
                    {{ $statement['endDate'] ? $statement['endDate']->format('M d, Y') : 'Present' }}
                </div>
            </td>
        </tr>
    </table>

    {{-- Account Details & Summary --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 4px;">Statement To</div>
                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">{{ $statement['client']->name }}</div>
                @if($statement['client']->company_name)
                    <div style="color: #334155; font-weight: 600; font-size: 11px;">{{ $statement['client']->company_name }}</div>
                @endif
                @if($statement['client']->address)
                    <div style="color: #475569; font-size: 11px;">{{ $statement['client']->address }}</div>
                @endif
                @if($statement['client']->city || $statement['client']->country)
                    <div style="color: #475569; font-size: 11px;">{{ $statement['client']->city }}{{ $statement['client']->city && $statement['client']->country ? ', ' : '' }}{{ $statement['client']->country }}</div>
                @endif
                @if($statement['client']->email)
                    <div style="color: #475569; font-size: 11px;">{{ $statement['client']->email }}</div>
                @endif
            </td>
            <td style="width: 50%; vertical-align: top;">
                <table style="width: 100%; border-collapse: collapse; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                    <tr>
                        <td style="padding: 6px 12px; font-size: 11px; color: #64748b;">Opening Balance:</td>
                        <td style="padding: 6px 12px; font-size: 11px; font-weight: 600; text-align: right; color: #0f172a;">{{ \App\Support\Currency::format($statement['openingBalance'], $statement['currency']) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 12px; font-size: 11px; color: #64748b;">Invoiced Amount:</td>
                        <td style="padding: 6px 12px; font-size: 11px; font-weight: 600; text-align: right; color: #0f172a;">+{{ \App\Support\Currency::format($statement['totalInvoiced'], $statement['currency']) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 12px; font-size: 11px; color: #64748b;">Amount Paid:</td>
                        <td style="padding: 6px 12px; font-size: 11px; font-weight: 600; text-align: right; color: #16a34a;">-{{ \App\Support\Currency::format($statement['totalPaid'], $statement['currency']) }}</td>
                    </tr>
                    <tr style="border-top: 2px solid #cbd5e1; background-color: #f1f5f9;">
                        <td style="padding: 8px 12px; font-size: 12px; font-weight: 700; color: #0f172a;">Current Balance Due:</td>
                        <td style="padding: 8px 12px; font-size: 13px; font-weight: 800; text-align: right; color: #2563eb;">{{ \App\Support\Currency::format($statement['closingBalance'], $statement['currency']) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Activity Table --}}
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
        <thead>
            <tr style="background-color: #0f172a; color: #ffffff;">
                <th style="padding: 8px 10px; font-size: 10px; font-weight: 700; text-align: left; text-transform: uppercase;">Date</th>
                <th style="padding: 8px 10px; font-size: 10px; font-weight: 700; text-align: left; text-transform: uppercase;">Activity / Reference</th>
                <th style="padding: 8px 10px; font-size: 10px; font-weight: 700; text-align: left; text-transform: uppercase;">Details</th>
                <th style="padding: 8px 10px; font-size: 10px; font-weight: 700; text-align: right; text-transform: uppercase;">Billed (+)</th>
                <th style="padding: 8px 10px; font-size: 10px; font-weight: 700; text-align: right; text-transform: uppercase;">Paid (-)</th>
                <th style="padding: 8px 10px; font-size: 10px; font-weight: 700; text-align: right; text-transform: uppercase;">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 8px 10px; font-size: 11px; color: #64748b;">
                    {{ $statement['startDate'] ? $statement['startDate']->format('M d, Y') : '-' }}
                </td>
                <td style="padding: 8px 10px; font-size: 11px; font-weight: 700; color: #0f172a;" colspan="4">
                    Starting / Opening Balance
                </td>
                <td style="padding: 8px 10px; font-size: 11px; font-weight: 700; text-align: right; color: #0f172a;">
                    {{ \App\Support\Currency::format($statement['openingBalance'], $statement['currency']) }}
                </td>
            </tr>

            @forelse($statement['ledger'] as $entry)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 8px 10px; font-size: 11px; color: #334155; white-space: nowrap;">
                    {{ $entry['date']->format('M d, Y') }}
                </td>
                <td style="padding: 8px 10px; font-size: 11px; font-weight: 600; color: #0f172a;">
                    <span class="badge badge-{{ $entry['type'] }}">{{ strtoupper($entry['type']) }}</span>
                    <span style="margin-left: 4px;">{{ $entry['reference'] }}</span>
                </td>
                <td style="padding: 8px 10px; font-size: 11px; color: #64748b;">
                    {{ $entry['description'] }}
                </td>
                <td style="padding: 8px 10px; font-size: 11px; text-align: right; font-weight: 600; color: #0f172a;">
                    {{ $entry['debit'] > 0 ? \App\Support\Currency::format($entry['debit'], $statement['currency']) : '-' }}
                </td>
                <td style="padding: 8px 10px; font-size: 11px; text-align: right; font-weight: 600; color: #16a34a;">
                    {{ $entry['credit'] > 0 ? '-' . \App\Support\Currency::format($entry['credit'], $statement['currency']) : '-' }}
                </td>
                <td style="padding: 8px 10px; font-size: 11px; text-align: right; font-weight: 700; color: #0f172a;">
                    {{ \App\Support\Currency::format($entry['balance'], $statement['currency']) }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 20px; text-align: center; color: #94a3b8; font-style: italic;">
                    No invoice or payment activity recorded in this selected period.
                </td>
            </tr>
            @endforelse

            {{-- Closing Balance Row --}}
            <tr style="background-color: #0f172a; color: #ffffff;">
                <td colspan="5" style="padding: 10px; font-size: 11px; font-weight: 700; text-transform: uppercase;">
                    Closing Balance Due:
                </td>
                <td style="padding: 10px; font-size: 12px; font-weight: 800; text-align: right; color: #ffffff;">
                    {{ \App\Support\Currency::format($statement['closingBalance'], $statement['currency']) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div style="border-top: 1px solid #e2e8f0; padding-top: 15px; margin-top: 25px; text-align: center; font-size: 10px; color: #94a3b8;">
        Thank you for your business! Please settle any outstanding balance using your online invoice portal or standard payment methods.
    </div>
</body>
</html>