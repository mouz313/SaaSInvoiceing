<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estimate #{{ $estimate->estimate_number }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #312e81 0%, #4338ca 100%); color: #ffffff; padding: 30px; text-align: left; }
        .brand-title { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
        .body-content { padding: 30px; }
        .amount-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; margin: 25px 0; }
        .amount-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
        .amount-value { font-size: 32px; font-weight: 800; color: #312e81; margin: 4px 0 0 0; }
        .btn { display: inline-block; background-color: #4f46e5; color: #ffffff !important; font-weight: 700; font-size: 14px; text-decoration: none; padding: 14px 28px; border-radius: 10px; margin: 10px 0; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; }
        .details-table td { padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1 class="brand-title">{{ $estimate->user->company_name ?: $estimate->user->name }}</h1>
            <div style="font-size: 12px; color: #c7d2fe; margin-top: 4px;">Commercial Proposal &amp; Quotation</div>
        </div>

        <div class="body-content">
            <p style="font-size: 15px; font-weight: 600; margin-top: 0;">Hello {{ $estimate->client->name }},</p>

            @if(!empty($customMessage))
                <div style="background-color: #f1f5f9; padding: 14px 18px; border-radius: 10px; font-size: 13px; color: #334155; margin-bottom: 20px; border-left: 3px solid #4f46e5;">
                    {{ $customMessage }}
                </div>
            @else
                <p style="font-size: 13px; color: #475569;">
                    Please find attached our quotation proposal <strong>#{{ $estimate->estimate_number }}</strong> for the requested products/services.
                </p>
            @endif

            <div class="amount-card">
                <div class="amount-label">Quoted Total Estimate</div>
                <div class="amount-value">{{ $estimate->currency }} {{ number_format($estimate->total, 2) }}</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 6px;">Valid until <strong>{{ $estimate->expiry_date->format('M d, Y') }}</strong></div>

                <div style="margin-top: 18px;">
                    <a href="{{ $estimate->public_url }}" class="btn">Review Proposal &amp; Accept Online &rarr;</a>
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <td style="color: #64748b;">Estimate Number:</td>
                    <td class="text-right" style="font-weight: 600;">#{{ $estimate->estimate_number }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Issue Date:</td>
                    <td class="text-right">{{ $estimate->estimate_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Valid Until:</td>
                    <td class="text-right">{{ $estimate->expiry_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Proposal Status:</td>
                    <td class="text-right" style="font-weight: 700; text-transform: uppercase; color: #4f46e5;">{{ $estimate->status }}</td>
                </tr>
            </table>

            <p style="font-size: 12px; color: #64748b; margin-top: 20px;">
                📎 A copy of the PDF estimate proposal has been attached to this email. You can accept or decline the proposal directly from the online link.
            </p>
        </div>

        <div class="footer">
            Sent via {{ config('app.name', 'InvoiceHub') }} on behalf of {{ $estimate->user->name }} ({{ $estimate->user->email }}).
        </div>
    </div>
</body>
</html>
