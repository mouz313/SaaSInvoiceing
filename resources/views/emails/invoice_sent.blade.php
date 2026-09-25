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

            <!-- Client Portal Credentials Section -->
            @if($invoice->client && $invoice->client->email)
            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 18px 20px; margin: 25px 0; text-align: left;">
                <div style="font-size: 13px; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                    🔑 Your Client Portal Credentials
                </div>
                <p style="font-size: 12px; color: #3b82f6; margin: 0 0 12px 0;">
                    You have a secure online portal to view invoices, download payment receipts, accept proposals, and track your account balance at any time.
                </p>
                <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
                    <tr>
                        <td style="color: #64748b; padding: 4px 0; width: 120px;"><strong>Portal Link:</strong></td>
                        <td style="padding: 4px 0;"><a href="{{ route('portal.login') }}" style="color: #2563eb; font-weight: 700; text-decoration: underline;">{{ route('portal.login') }}</a></td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;"><strong>Username:</strong></td>
                        <td style="color: #0f172a; font-weight: 600; padding: 4px 0;">{{ $invoice->client->email }}</td>
                    </tr>
                    @if(!empty($tempPassword) || (!empty($invoice->client->temp_password)))
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;"><strong>Initial Password:</strong></td>
                        <td style="padding: 4px 0;">
                            <span style="font-family: monospace; font-size: 13px; font-weight: 700; background: #ffffff; padding: 2px 6px; border-radius: 4px; border: 1px solid #cbd5e1; color: #0f172a;">
                                {{ $tempPassword ?: $invoice->client->temp_password }}
                            </span>
                            <span style="font-size: 11px; color: #64748b; margin-left: 6px;">(You will be asked to choose your own password upon first login)</span>
                        </td>
                    </tr>
                    @else
                    <tr>
                        <td style="color: #64748b; padding: 4px 0;"><strong>Password:</strong></td>
                        <td style="color: #64748b; font-style: italic; padding: 4px 0;">
                            Use your existing password, or request a passwordless login link on the portal.
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
            @endif

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
