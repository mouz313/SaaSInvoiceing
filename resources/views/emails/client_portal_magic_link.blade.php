<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Portal Access</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; line-height: 1.6; }
        .wrapper { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; }
        .header { background-color: #0f172a; color: #ffffff; padding: 30px; text-align: left; }
        .brand-title { font-size: 20px; font-weight: 800; letter-spacing: -0.5px; margin: 0; }
        .body-content { padding: 30px; }
        .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; font-weight: 700; font-size: 14px; text-decoration: none; padding: 14px 28px; border-radius: 10px; margin: 15px 0; }
        .footer { background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1 class="brand-title">{{ $client->user->company_name ?: $client->user->name }}</h1>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Client Account Portal</div>
        </div>

        <div class="body-content">
            <p style="font-size: 15px; font-weight: 600; margin-top: 0;">Hello {{ $client->name }},</p>
            <p style="font-size: 13px; color: #475569;">
                Here is your private, secure link to access your client portal. No password is required.
            </p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $client->portal_url }}" class="btn">Access Client Portal &rarr;</a>
            </div>

            <p style="font-size: 13px; color: #475569;">
                Inside your portal, you can:
            </p>
            <ul style="font-size: 13px; color: #475569; padding-left: 20px; line-height: 1.8;">
                <li>View all invoices and pay outstanding balances online</li>
                <li>Review and accept/decline project estimates and quotes</li>
                <li>Download payment receipts and official PDFs</li>
                <li>View and export your real-time account ledger and statements</li>
            </ul>

            <p style="font-size: 11px; color: #94a3b8; margin-top: 25px;">
                If you did not request this link, you can safely ignore this email.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ $client->user->company_name ?: $client->user->name }}. All rights reserved.
        </div>
    </div>
</body>
</html>