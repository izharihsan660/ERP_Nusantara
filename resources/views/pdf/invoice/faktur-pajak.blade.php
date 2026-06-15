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
        .meta td { border: 0; padding: 3px 0; }
        .right { text-align: right; }
        .total { font-weight: bold; background: #f9fafb; }
        .box { border: 1px solid #d1d5db; padding: 10px; margin-top: 14px; }
    </style>
</head>
<body>
    <h1>FAKTUR PAJAK</h1>

    <div class="box">
        <table class="meta">
            <tr>
                <td style="width: 160px;">Kode dan Nomor Seri</td>
                <td>: {{ $invoice->no_faktur_pajak ?: '-' }}</td>
            </tr>
            <tr>
                <td>Pengusaha Kena Pajak</td>
                <td>: PT. Nusantara Abadi Jaya</td>
            </tr>
            <tr>
                <td>Pembeli BKP/JKP</td>
                <td>: {{ $invoice->customer?->nama_customer }}</td>
            </tr>
            <tr>
                <td>Tanggal Dokumen</td>
                <td>: {{ $invoice->tgl_dokumen?->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <table style="margin-top: 18px;">
        <thead>
            <tr>
                <th>Nama Barang Kena Pajak</th>
                <th class="right" style="width: 70px;">Qty</th>
                <th class="right" style="width: 120px;">Harga Satuan</th>
                <th class="right" style="width: 130px;">Harga Jual</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item['deskripsi'] }}</td>
                    <td class="right">{{ number_format($item['qty'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($item['harga_satuan'], 2, ',', '.') }}</td>
                    <td class="right">{{ number_format($item['jumlah'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3">Dasar Pengenaan Pajak</td>
                <td class="right">{{ number_format((float) $invoice->total_nilai, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3">PPN</td>
                <td class="right">-</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
