<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #0f172a; color: #ffffff; padding: 30px; text-align: left; }
        .brand-title { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
        .body-content { padding: 30px; }
        .amount-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; margin: 25px 0; }
        .amount-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
        .amount-value { font-size: 32px; font-weight: 800; color: #0f172a; margin: 4px 0 0 0; }
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
            <h1 class="brand-title">{{ $invoice->user->company_name ?: $invoice->user->name }}</h1>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Official Invoice Statement</div>
        </div>

        <div class="body-content">
            <p style="font-size: 15px; font-weight: 600; margin-top: 0;">Hello {{ $invoice->client->name }},</p>

            @if(!empty($customMessage))
                <div style="background-color: #f1f5f9; padding: 14px 18px; border-radius: 10px; font-size: 13px; color: #334155; margin-bottom: 20px; border-left: 3px solid #2563eb;">
                    {{ $customMessage }}
                </div>
            @else
                <p style="font-size: 13px; color: #475569;">
                    Please find attached your invoice <strong>#{{ $invoice->invoice_number }}</strong> for recent products/services delivered.
                </p>
            @endif

            <div class="amount-card">
                <div class="amount-label">Total Amount Due</div>
                <div class="amount-value">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</div>
                <div style="font-size: 12px; color: #64748b; margin-top: 6px;">Due by <strong>{{ $invoice->due_date->format('M d, Y') }}</strong></div>

                <div style="margin-top: 18px;">
                    <a href="{{ $invoice->public_url }}" class="btn">View &amp; Pay Invoice Online &rarr;</a>
                </div>

                <div style="margin-top: 15px; font-size: 12px; color: #64748b;">
                    View all past invoices, receipts &amp; statements in your <a href="{{ $invoice->client->portal_url }}" style="color: #2563eb; font-weight: 600; text-decoration: underline;">Client Portal &rarr;</a>
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <td style="color: #64748b;">Invoice Number:</td>
                    <td class="text-right" style="font-weight: 600;">#{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Issue Date:</td>
                    <td class="text-right">{{ $invoice->invoice_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Due Date:</td>
                    <td class="text-right">{{ $invoice->due_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Payment Status:</td>
                    <td class="text-right" style="font-weight: 700; text-transform: uppercase; color: {{ $invoice->isPaid() ? '#15803d' : '#2563eb' }};">{{ $invoice->status }}</td>
                </tr>
            </table>

            <p style="font-size: 12px; color: #64748b; margin-top: 20px;">
                📎 A copy of the PDF invoice has been attached to this email for your accounting records.
            </p>
        </div>

        <div class="footer">
            Sent via {{ config('app.name', 'InvoiceHub') }} on behalf of {{ $invoice->user->name }} ({{ $invoice->user->email }}).
        </div>
    </div>
</body>
</html>
