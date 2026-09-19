<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Statement</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #0f172a; color: #ffffff; padding: 30px; text-align: left; }
        .brand-title { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
        .body-content { padding: 30px; }
        .statement-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; margin: 25px 0; }
        .statement-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
        .statement-value { font-size: 32px; font-weight: 800; color: #0f172a; margin: 4px 0 0 0; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; font-weight: 700; font-size: 14px; text-decoration: none; padding: 14px 28px; border-radius: 10px; margin: 10px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; }
        .details-table td { padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1 class="brand-title">{{ $client->user->company_name ?: $client->user->name }}</h1>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Official Account Statement</div>
        </div>

        <div class="body-content">
            <p style="font-size: 15px; font-weight: 600; margin-top: 0;">Hello {{ $client->name }},</p>

            @if(!empty($customMessage))
                <div style="background-color: #f1f5f9; padding: 14px 18px; border-radius: 10px; font-size: 13px; color: #334155; margin-bottom: 20px; border-left: 3px solid #2563eb;">
                    {{ $customMessage }}
                </div>
            @else
                <p style="font-size: 13px; color: #475569;">
                    Please find attached your account statement summarizing all invoice and payment activity.
                </p>
            @endif

            <div class="statement-card">
                <div class="statement-label">Current Outstanding Balance</div>
                <div class="statement-value">{{ \App\Support\Currency::format($statementData['closingBalance'], $statementData['currency']) }}</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 6px;">
                    Period: 
                    <strong>
                        {{ $statementData['startDate'] ? $statementData['startDate']->format('M d, Y') : 'Start of Account' }}
                        &mdash;
                        {{ $statementData['endDate'] ? $statementData['endDate']->format('M d, Y') : 'Present' }}
                    </strong>
                </div>

                <div style="margin-top: 18px;">
                    <a href="{{ $client->portal_url }}" class="btn">Open Client Portal &rarr;</a>
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <td style="color: #64748b;">Opening Balance:</td>
                    <td class="text-right" style="font-weight: 600;">{{ \App\Support\Currency::format($statementData['openingBalance'], $statementData['currency']) }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Total Invoiced in Period:</td>
                    <td class="text-right" style="font-weight: 600;">+{{ \App\Support\Currency::format($statementData['totalInvoiced'], $statementData['currency']) }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Total Payments in Period:</td>
                    <td class="text-right" style="font-weight: 600; color: #16a34a;">-{{ \App\Support\Currency::format($statementData['totalPaid'], $statementData['currency']) }}</td>
                </tr>
                <tr>
                    <td style="color: #0f172a; font-weight: 700;">Closing Balance Due:</td>
                    <td class="text-right" style="font-weight: 800; color: #2563eb; font-size: 15px;">{{ \App\Support\Currency::format($statementData['closingBalance'], $statementData['currency']) }}</td>
                </tr>
            </table>

            <p style="font-size: 12px; color: #94a3b8; text-align: center; margin-top: 25px;">
                A PDF copy of this statement has been attached to this email. You can also view past invoices and pay online securely anytime through your portal.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ $client->user->company_name ?: $client->user->name }}. All rights reserved.
        </div>
    </div>
</body>
</html>