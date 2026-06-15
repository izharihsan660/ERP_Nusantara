<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $purchaseOrder->no_purchase_order }}</title>
    <style>
        body { color: #0f172a; font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.45; }
        .header { border-bottom: 2px solid #0f172a; margin-bottom: 22px; padding-bottom: 12px; }
        .company { font-size: 18px; font-weight: bold; letter-spacing: .4px; }
        .muted { color: #475569; }
        .title { font-size: 20px; font-weight: bold; margin: 18px 0 8px; text-align: center; }
        .meta { margin-bottom: 18px; width: 100%; }
        .meta td { padding: 3px 0; vertical-align: top; }
        table.items { border-collapse: collapse; width: 100%; }
        table.items th, table.items td { border: 1px solid #cbd5e1; padding: 7px; }
        table.items th { background: #f1f5f9; text-align: left; }
        .right { text-align: right; }
        .summary { margin-left: auto; margin-top: 14px; width: 260px; }
        .summary td { padding: 5px 0; }
        .summary .grand td { border-top: 1px solid #0f172a; font-weight: bold; padding-top: 8px; }
        .signature { margin-top: 34px; width: 100%; }
        .signature td { text-align: center; width: 50%; }
        .signature .space { height: 54px; }
        .footer { bottom: 20px; color: #475569; font-size: 10px; position: fixed; right: 0; width: 170px; }
        .qr { margin-left: auto; width: 96px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PT. Nusantara Abadi Jaya</div>
        <div class="muted">Makassar - Dokumen Purchase Order</div>
        <div class="muted">Jl. Nusantara Abadi Jaya, Makassar</div>
    </div>

    <div class="title">PURCHASE ORDER</div>

    <table class="meta">
        <tr>
            <td width="130">No. PO</td>
            <td width="10">:</td>
            <td>{{ $purchaseOrder->no_purchase_order }}</td>
            <td width="130">Tanggal</td>
            <td width="10">:</td>
            <td>{{ $purchaseOrder->tgl_po?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Kepada</td>
            <td>:</td>
            <td>{{ $purchaseOrder->vendor?->nama_vendor }}</td>
            <td>No. PR Customer</td>
            <td>:</td>
            <td>{{ $purchaseOrder->no_pr_customer ?: '-' }}</td>
        </tr>
        <tr>
            <td>Alamat Vendor</td>
            <td>:</td>
            <td>{{ $purchaseOrder->vendor?->alamat ?: '-' }}</td>
            <td>No. PO Customer</td>
            <td>:</td>
            <td>{{ $purchaseOrder->no_po_customer ?: '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th width="35">No</th>
                <th>Deskripsi</th>
                <th class="right">Qty</th>
                <th>Satuan</th>
                <th class="right">Harga Satuan</th>
                <th class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchaseOrder->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->deskripsi }}</td>
                    <td class="right">{{ number_format($item->qty, 0, ',', '.') }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td class="right">{{ number_format((float) $item->harga_satuan, 2, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $item->jumlah, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr class="grand">
            <td>Total</td>
            <td class="right">{{ number_format($purchaseOrder->total, 2, ',', '.') }}</td>
        </tr>
    </table>

    @if ($purchaseOrder->catatan)
        <p><strong>Catatan:</strong> {{ $purchaseOrder->catatan }}</p>
    @endif

    <table class="signature">
        <tr>
            <td>Dibuat oleh,</td>
            <td>Disetujui,</td>
        </tr>
        <tr>
            <td class="space"></td>
            <td class="space"></td>
        </tr>
        <tr>
            <td>{{ $purchaseOrder->createdBy?->name ?: 'PT. Nusantara Abadi Jaya' }}</td>
            <td>{{ $purchaseOrder->approvedBy?->name ?: 'Manager' }}</td>
        </tr>
    </table>

    <div class="footer">
        <img class="qr" src="{{ $qrCode }}" alt="QR Verifikasi">
        <div>Scan untuk verifikasi dokumen.</div>
    </div>
</body>
</html>
