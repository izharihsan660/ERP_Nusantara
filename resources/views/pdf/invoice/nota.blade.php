<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { margin: 0; font-size: 24px; letter-spacing: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 7px; }
        th { background: #f3f4f6; text-align: left; }
        .header { margin-bottom: 24px; }
        .company { font-size: 16px; font-weight: bold; }
        .meta td { border: 0; padding: 3px 0; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #f9fafb; }
        .signature { margin-top: 36px; }
        .signature td { height: 86px; vertical-align: bottom; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PT. Nusantara Abadi Jaya</div>
        <div>Distributor spare part & pallet</div>
        <div>Makassar, Sulawesi Selatan</div>
    </div>

    <h1>NOTA PENJUALAN</h1>
    <table class="meta" style="margin-top: 12px;">
        <tr>
            <td style="width: 120px;">No. Dokumen</td>
            <td>: {{ $invoice->no_dokumen }}</td>
            <td style="width: 120px;">Tanggal</td>
            <td>: {{ $invoice->tgl_dokumen?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Kepada</td>
            <td>: {{ $invoice->customer?->nama_customer }}</td>
            <td>Faktur Pajak</td>
            <td>: {{ $invoice->no_faktur_pajak ?: '-' }}</td>
        </tr>
        <tr>
            <td>Metode Bayar</td>
            <td>: {{ $invoice->metode_pembayaran->label() }}</td>
            <td>No. SPB</td>
            <td>: {{ $invoice->spb?->no_spb }}</td>
        </tr>
    </table>

    <table style="margin-top: 18px;">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th class="right" style="width: 70px;">Qty</th>
                <th style="width: 70px;">Satuan</th>
                <th class="right" style="width: 120px;">Harga</th>
                <th class="right" style="width: 130px;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['deskripsi'] }}</td>
                    <td class="right">{{ number_format($item['qty'], 0, ',', '.') }}</td>
                    <td>{{ $item['satuan'] }}</td>
                    <td class="right">{{ number_format($item['harga_satuan'], 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($item['jumlah'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="4">Total</td>
                <td class="right">{{ number_format((float) $invoice->total_nilai, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature">
        <tr>
            <td>Finance<br><br><br>(________________)</td>
            <td>Customer<br><br><br>(________________)</td>
        </tr>
    </table>
</body>
</html>
