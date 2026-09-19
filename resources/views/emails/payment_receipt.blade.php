<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #065f46 0%, #059669 100%); color: #ffffff; padding: 32px 30px; text-align: left; }
        .brand-title { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
        .body-content { padding: 30px; }
        .receipt-card { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 22px; text-align: center; margin: 24px 0; }
        .receipt-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #065f46; letter-spacing: 1px; }
        .receipt-value { font-size: 32px; font-weight: 800; color: #047857; margin: 4px 0 0 0; }
        .btn { display: inline-block; background-color: #059669; color: #ffffff !important; font-weight: 700; font-size: 14px; text-decoration: none; padding: 12px 24px; border-radius: 10px; margin: 12px 0 4px 0; }
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
            <div style="font-size: 13px; color: #d1fae5; margin-top: 4px;">Official Payment Receipt &bull; PAID IN FULL</div>
        </div>

        <div class="body-content">
            <p style="font-size: 15px; font-weight: 600; margin-top: 0;">Hello {{ $invoice->client->name }},</p>

            <p style="font-size: 13px; color: #475569;">
                Thank you for your business! This email confirms that full payment for invoice <strong>#{{ $invoice->invoice_number }}</strong> has been successfully received and recorded.
            </p>

            <div class="receipt-card">
                <div class="receipt-label">Amount Paid</div>
                <div class="receipt-value">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</div>
                <div style="font-size: 12px; color: #047857; margin-top: 6px; font-weight: 600;">Status: PAID &bull; {{ $paymentMethod }}</div>

                <div style="margin-top: 16px;">
                    <a href="{{ $invoice->public_url }}" class="btn">View Statement &amp; Receipt Online &rarr;</a>
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <td style="color: #64748b;">Invoice Reference:</td>
                    <td class="text-right" style="font-weight: 600;">#{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Paid Date:</td>
                    <td class="text-right">{{ now()->format('M d, Y - h:i A') }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Payment Method:</td>
                    <td class="text-right" style="font-weight: 600;">{{ $paymentMethod }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Remaining Balance:</td>
                    <td class="text-right" style="font-weight: 700; color: #16a34a;">{{ $invoice->currency }} 0.00</td>
                </tr>
            </table>

            <p style="font-size: 12px; color: #64748b; margin-top: 20px;">
                📎 A copy of the marked-as-paid PDF invoice has been attached to this receipt for your accounting records.
            </p>
        </div>

        <div class="footer">
            Issued by {{ $invoice->user->name }} ({{ $invoice->user->email }}).<br>
            Powered by {{ setting('app_name', config('app.name', 'InvoiceHub')) }}.
        </div>
    </div>
</body>
</html>
