<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { margin: 0; font-size: 22px; text-align: center; letter-spacing: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 7px; }
        th { background: #f3f4f6; text-align: left; }
        .meta td { border: 0; padding: 4px 0; }
        .signature { margin-top: 40px; }
        .signature td { height: 100px; vertical-align: bottom; text-align: center; }
    </style>
</head>
<body>
    <h1>TANDA TERIMA</h1>

    <table class="meta" style="margin-top: 22px;">
        <tr>
            <td style="width: 160px;">Telah diterima dari</td>
            <td>: PT. Nusantara Abadi Jaya</td>
        </tr>
        <tr>
            <td>Oleh</td>
            <td>: {{ $invoice->customer?->nama_customer }}</td>
        </tr>
        <tr>
            <td>No. Dokumen</td>
            <td>: {{ $invoice->no_dokumen }}</td>
        </tr>
        <tr>
            <td>No. SPB</td>
            <td>: {{ $invoice->spb?->no_spb }}</td>
        </tr>
        <tr>
            <td>Jenis Dokumen</td>
            <td>: {{ $invoice->tipe_dokumen->label() }}, Faktur Pajak, dan dokumen pendukung pengiriman</td>
        </tr>
        <tr>
            <td>Total Tagihan</td>
            <td>: Rp {{ number_format((float) $invoice->total_nilai, 2, ',', '.') }}</td>
        </tr>
    </table>

    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Deskripsi Barang</th>
                <th style="width: 80px;">Qty</th>
                <th style="width: 80px;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['deskripsi'] }}</td>
                    <td>{{ number_format($item['qty'], 0, ',', '.') }}</td>
                    <td>{{ $item['satuan'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature">
        <tr>
            <td>Diserahkan oleh<br><br><br>(________________)</td>
            <td>Diterima oleh<br><br><br>(________________)</td>
        </tr>
    </table>
</body>
</html>
