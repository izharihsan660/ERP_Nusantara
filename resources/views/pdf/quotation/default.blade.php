<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->no_quotation }}</title>
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
        .footer { bottom: 20px; color: #475569; font-size: 10px; position: fixed; right: 0; width: 160px; }
        .qr { margin-left: auto; width: 96px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PT. Nusantara Abadi Jaya</div>
        <div class="muted">Makassar - Dokumen Quotation</div>
    </div>

    <div class="title">QUOTATION</div>

    <table class="meta">
        <tr>
            <td width="120">No. Quotation</td>
            <td width="10">:</td>
            <td>{{ $quotation->no_quotation }}</td>
            <td width="120">Tanggal</td>
            <td width="10">:</td>
            <td>{{ $quotation->tgl_quotation?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Customer</td>
            <td>:</td>
            <td>{{ $quotation->customer?->nama_customer }}</td>
            <td>Revisi</td>
            <td>:</td>
            <td>{{ $quotation->revisi }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th width="35">No</th>
                <th>Part No</th>
                <th>Deskripsi</th>
                <th class="right">Qty</th>
                <th>Satuan</th>
                <th class="right">Harga</th>
                <th class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->part_no }}</td>
                    <td>{{ $item->deskripsi }}</td>
                    <td class="right">{{ number_format($item->qty, 0, ',', '.') }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td class="right">{{ number_format((float) $item->harga_satuan, 0, ',', '.') }}</td>
                    <td class="right">{{ number_format((float) $item->jumlah, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr class="grand">
            <td>Total</td>
            <td class="right">{{ number_format($quotation->total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p style="margin-top: 28px;">Hormat kami,</p>
    <p style="margin-top: 56px;">PT. Nusantara Abadi Jaya</p>

    <div class="footer">
        <img class="qr" src="{{ $qrCode }}" alt="QR Verifikasi">
        <div>Scan untuk verifikasi dokumen.</div>
    </div>
</body>
</html>
