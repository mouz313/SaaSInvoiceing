<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ setting('app_name', config('app.name', 'InvoiceHub')) }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #ffffff; padding: 36px 30px; text-align: center; }
        .brand-title { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
        .body-content { padding: 32px 30px; }
        .feature-card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0; }
        .feature-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #166534; letter-spacing: 1px; }
        .feature-value { font-size: 28px; font-weight: 800; color: #15803d; margin: 4px 0 0 0; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; font-weight: 700; font-size: 14px; text-decoration: none; padding: 14px 28px; border-radius: 10px; margin: 16px 0; }
        .step-list { margin: 24px 0; padding: 0; list-style: none; font-size: 14px; }
        .step-item { padding: 10px 0; border-bottom: 1px solid #f1f5f9; display: flex; align-items: flex-start; gap: 10px; }
        .step-num { background: #eff6ff; color: #2563eb; font-weight: 800; width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1 class="brand-title">{{ setting('app_name', config('app.name', 'InvoiceHub')) }}</h1>
            <div style="font-size: 13px; color: #bfdbfe; margin-top: 6px;">Executive Billing &amp; Invoice Suite</div>
        </div>

        <div class="body-content">
            <p style="font-size: 16px; font-weight: 700; margin-top: 0; color: #0f172a;">Welcome aboard, {{ $user->name }}! 👋</p>

            <p style="font-size: 14px; color: #475569;">
                Thank you for joining {{ setting('app_name', config('app.name', 'InvoiceHub')) }}. Your account has been activated and is ready to generate designer-grade invoices, estimates, and manage payments.
            </p>

            <div class="feature-card">
                <div class="feature-label">Promotional Starter Credit</div>
                <div class="feature-value">{{ $user->invoice_credits }} Free Invoices</div>
                <div style="font-size: 12px; color: #166534; margin-top: 6px;">Granted automatically to your account balance!</div>
            </div>

            <div style="text-align: center;">
                <a href="{{ route('invoices.create') }}" class="btn">Create Your First Invoice &rarr;</a>
            </div>

            <p style="font-size: 13px; font-weight: 700; color: #334155; margin-top: 24px;">3 Quick Steps to Get Started:</p>
            <div class="step-list">
                <div class="step-item">
                    <span class="step-num">1</span>
                    <div><strong>Set up Company Profile &amp; Logo:</strong> Add your business name, address, and upload your brand logo in profile settings.</div>
                </div>
                <div class="step-item">
                    <span class="step-num">2</span>
                    <div><strong>Add Your Clients &amp; Products:</strong> Save clients and products with default pricing for 1-click invoice line addition.</div>
                </div>
                <div class="step-item">
                    <span class="step-num">3</span>
                    <div><strong>Send Invoices &amp; Get Paid:</strong> Pick between 4 executive aesthetics (Corporate, Minimalist, Creative, Grid) and accept Stripe card payments.</div>
                </div>
            </div>

            <p style="font-size: 12px; color: #64748b; margin-top: 24px;">
                Need assistance? Reply directly to this email or reach us at <a href="mailto:{{ setting('support_email', 'support@invoicehub.test') }}" style="color: #2563eb;">{{ setting('support_email', 'support@invoicehub.test') }}</a>.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ setting('app_name', config('app.name', 'InvoiceHub')) }}. All rights reserved.<br>
            {{ setting('office_address', 'Global Invoicing Platform') }}
        </div>
    </div>
</body>
</html>
