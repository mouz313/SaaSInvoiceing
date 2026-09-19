<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Reminder #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header-upcoming { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; padding: 30px; text-align: left; }
        .header-due_today { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); color: #ffffff; padding: 30px; text-align: left; }
        .header-overdue { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: #ffffff; padding: 30px; text-align: left; }
        .brand-title { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
        .body-content { padding: 30px; }
        .reminder-card { border-radius: 12px; padding: 22px; text-align: center; margin: 24px 0; }
        .card-upcoming { background: #eff6ff; border: 1px solid #bfdbfe; }
        .card-due_today { background: #fffbeb; border: 1px solid #fde68a; }
        .card-overdue { background: #fef2f2; border: 1px solid #fecaca; }
        .label-upcoming { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #1d4ed8; letter-spacing: 1px; }
        .label-due_today { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #b45309; letter-spacing: 1px; }
        .label-overdue { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #b91c1c; letter-spacing: 1px; }
        .value-upcoming { font-size: 32px; font-weight: 800; color: #1e40af; margin: 4px 0 0 0; }
        .value-due_today { font-size: 32px; font-weight: 800; color: #92400e; margin: 4px 0 0 0; }
        .value-overdue { font-size: 32px; font-weight: 800; color: #991b1b; margin: 4px 0 0 0; }
        .btn { display: inline-block; font-weight: 700; font-size: 14px; text-decoration: none; padding: 13px 26px; border-radius: 10px; margin: 12px 0 4px 0; }
        .btn-upcoming { background-color: #2563eb; color: #ffffff !important; }
        .btn-due_today { background-color: #d97706; color: #ffffff !important; }
        .btn-overdue { background-color: #dc2626; color: #ffffff !important; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; }
        .details-table td { padding: 9px 0; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header-{{ $reminderType }}">
            <h1 class="brand-title">{{ $invoice->user->company_name ?: $invoice->user->name }}</h1>
            <div style="font-size: 13px; opacity: 0.9; margin-top: 4px;">
                @if($reminderType === 'due_today')
                    Notice: Invoice is due today
                @elseif($reminderType === 'overdue')
                    Attention: Invoice is overdue by {{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }}
                @else
                    Friendly Payment Reminder &bull; Due in 3 days
                @endif
            </div>
        </div>

        <div class="body-content">
            <p style="font-size: 15px; font-weight: 600; margin-top: 0;">Dear {{ $invoice->client->name }},</p>

            <p style="font-size: 13px; color: #475569;">
                @if($reminderType === 'due_today')
                    This is a reminder that payment for invoice <strong>#{{ $invoice->invoice_number }}</strong> is due today, <strong>{{ $invoice->due_date?->format('M d, Y') }}</strong>.
                @elseif($reminderType === 'overdue')
                    Our records indicate that we have not yet received payment for invoice <strong>#{{ $invoice->invoice_number }}</strong>, which was due on <strong>{{ $invoice->due_date?->format('M d, Y') }}</strong> ({{ $daysOverdue }} {{ Str::plural('day', $daysOverdue) }} ago).
                @else
                    This is a friendly reminder that invoice <strong>#{{ $invoice->invoice_number }}</strong> is due on <strong>{{ $invoice->due_date?->format('M d, Y') }}</strong>.
                @endif
            </p>

            <div class="reminder-card card-{{ $reminderType }}">
                <div class="label-{{ $reminderType }}">Outstanding Balance Due</div>
                <div class="value-{{ $reminderType }}">{{ $invoice->currency }} {{ number_format($invoice->balance_due ?? $invoice->total, 2) }}</div>
                @if(($invoice->amount_paid ?? 0) > 0)
                    <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Total: {{ $invoice->currency }} {{ number_format($invoice->total, 2) }} (Paid: {{ $invoice->currency }} {{ number_format($invoice->amount_paid, 2) }})</div>
                @endif

                <div style="margin-top: 16px;">
                    <a href="{{ $invoice->public_url }}" class="btn btn-{{ $reminderType }}">Pay Balance Online Now &rarr;</a>
                </div>
            </div>

            <table class="details-table">
                <tr>
                    <td style="color: #64748b;">Invoice Reference:</td>
                    <td class="text-right" style="font-weight: 600;">#{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Invoice Date:</td>
                    <td class="text-right">{{ $invoice->invoice_date?->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Due Date:</td>
                    <td class="text-right" style="font-weight: 600; {{ $reminderType === 'overdue' ? 'color: #dc2626;' : '' }}">{{ $invoice->due_date?->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Total Amount:</td>
                    <td class="text-right">{{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</td>
                </tr>
                <tr>
                    <td style="color: #64748b;">Balance Remaining:</td>
                    <td class="text-right" style="font-weight: 800; color: #0f172a;">{{ $invoice->currency }} {{ number_format($invoice->balance_due ?? $invoice->total, 2) }}</td>
                </tr>
            </table>

            @if($invoice->payment_instructions)
                <div style="background: #f8fafc; border-left: 4px solid #94a3b8; padding: 12px 16px; margin: 16px 0; border-radius: 4px;">
                    <strong style="font-size: 12px; color: #334155; text-transform: uppercase;">Payment Instructions:</strong>
                    <div style="font-size: 13px; color: #475569; margin-top: 4px; white-space: pre-line;">{{ $invoice->payment_instructions }}</div>
                </div>
            @endif

            <p style="font-size: 12px; color: #64748b; margin-top: 20px;">
                📎 A copy of the PDF invoice has been attached to this reminder for your convenience. If you have already made this payment, please disregard this notice.
            </p>
        </div>

        <div class="footer">
            Sent by {{ $invoice->user->name }} ({{ $invoice->user->email }}).<br>
            Powered by {{ setting('app_name', config('app.name', 'InvoiceHub')) }}.
        </div>
    </div>
</body>
</html>