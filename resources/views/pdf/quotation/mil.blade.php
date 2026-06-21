<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->no_quotation }}</title>
    <style>
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.45; }
        .header { border: 1px solid #111827; margin-bottom: 20px; padding: 14px; }
        .company { font-size: 18px; font-weight: bold; }
        .mil { background: #111827; color: #ffffff; display: inline-block; font-size: 11px; margin-top: 6px; padding: 3px 8px; }
        .title { font-size: 19px; font-weight: bold; margin: 18px 0; text-align: center; }
        .meta { margin-bottom: 18px; width: 100%; }
        .meta td { padding: 3px 0; vertical-align: top; }
        table.items { border-collapse: collapse; width: 100%; }
        table.items th, table.items td { border: 1px solid #9ca3af; padding: 7px; }
        table.items th { background: #111827; color: #ffffff; text-align: left; }
        .right { text-align: right; }
        .summary { margin-left: auto; margin-top: 14px; width: 260px; }
        .summary td { padding: 5px 0; }
        .summary .grand td { border-top: 1px solid #111827; font-weight: bold; padding-top: 8px; }
        .footer { bottom: 20px; color: #4b5563; font-size: 10px; position: fixed; right: 0; width: 160px; }
        .qr { margin-left: auto; width: 96px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PT. Nusantara Abadi Jaya</div>
        <div>Makassar Industrial Line</div>
        <div class="mil">MIL QUOTATION FORMAT</div>
    </div>

    <div class="title">QUOTATION - MIL</div>

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
                <th>Nama Barang</th>
                <th class="right">Qty</th>
                <th>UOM</th>
                <th class="right">Unit Price</th>
                <th class="right">Amount</th>
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
            <td>Grand Total</td>
            <td class="right">{{ number_format($quotation->total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p style="margin-top: 28px;">Approved by,</p>
    <p style="margin-top: 56px;">{{ $quotation->approvedBy?->name ?? 'PT. Nusantara Abadi Jaya' }}</p>

    <div class="footer">
        <img class="qr" src="{{ $qrCode }}" alt="QR Verifikasi">
        <div>Verified document: {{ $verifyUrl }}</div>
    </div>
</body>
</html>
