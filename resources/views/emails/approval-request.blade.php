<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Request</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f3f4f6;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .alert {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 4px;
        }
        .alert p {
            color: #92400e;
            font-size: 14px;
            font-weight: 600;
        }
        .document-info {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .document-info h2 {
            font-size: 18px;
            color: #111827;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .info-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #6b7280;
            width: 140px;
            font-size: 14px;
        }
        .info-value {
            color: #111827;
            flex: 1;
            font-size: 14px;
        }
        .buttons {
            margin: 32px 0;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
            margin: 8px;
            transition: all 0.2s;
        }
        .btn-approve {
            background-color: #10b981;
            color: #ffffff;
        }
        .btn-approve:hover {
            background-color: #059669;
        }
        .btn-reject {
            background-color: #ef4444;
            color: #ffffff;
        }
        .btn-reject:hover {
            background-color: #dc2626;
        }
        .notice {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 16px;
            margin: 24px 0;
        }
        .notice p {
            color: #1e40af;
            font-size: 13px;
            text-align: center;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            color: #6b7280;
            font-size: 13px;
            margin: 4px 0;
        }
        .footer .company {
            font-weight: 600;
            color: #111827;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 Approval Request</h1>
            <p>PT. Nusantara Abadi Jaya</p>
        </div>

        <div class="content">
            <div class="alert">
                <p>⚠️ Ada dokumen menunggu approval Anda</p>
            </div>

            <div class="document-info">
                <h2>Detail Dokumen</h2>
                <div class="info-row">
                    <span class="info-label">Tipe Dokumen</span>
                    <span class="info-value"><strong>{{ $documentType }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nomor</span>
                    <span class="info-value">{{ $documentNumber }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dibuat Oleh</span>
                    <span class="info-value">{{ $createdBy }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tanggal</span>
                    <span class="info-value">{{ $createdAt }}</span>
                </div>
                @if ($customer)
                <div class="info-row">
                    <span class="info-label">Customer/Tujuan</span>
                    <span class="info-value">{{ $customer }}</span>
                </div>
                @endif
                @if ($totalAmount)
                <div class="info-row">
                    <span class="info-label">Total Nilai</span>
                    <span class="info-value"><strong>{{ $totalAmount }}</strong></span>
                </div>
                @endif
            </div>

            <div class="buttons">
                <a href="{{ $approvalUrl }}" class="btn btn-approve">
                    ✅ APPROVE SEKARANG
                </a>
                <br>
                <a href="{{ $rejectUrl }}" class="btn btn-reject">
                    ❌ TOLAK
                </a>
            </div>

            <div class="notice">
                <p>🔒 Link approval berlaku selama 7 hari dari waktu pengiriman email ini.</p>
            </div>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh sistem ERP.</p>
            <p>Jika Anda memiliki pertanyaan, silakan hubungi tim internal.</p>
            <p class="company">PT. Nusantara Abadi Jaya</p>
        </div>
    </div>
</body>
</html>
