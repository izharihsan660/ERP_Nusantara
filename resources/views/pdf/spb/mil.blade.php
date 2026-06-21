@php
    $tanggal = $spb->tgl_spb ? \Carbon\Carbon::parse($spb->tgl_spb) : null;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 4px 8px; vertical-align: top; }
        .header td { border: 1px solid #ccc; }
        .company { font-size: 13px; font-weight: bold; }
        .doc-title { font-size: 16px; font-weight: bold; text-align: center; }
        .ship td { border: 1px solid #ccc; height: 18px; }
        .items th, .items td { border: 1px solid #ccc; padding: 3px 5px; }
        .items th { background: #f5f5f5; font-weight: bold; text-align: center; font-size: 9px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .small { font-size: 10px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 52%;">
                <div class="company">PT NUSANTARA ABADI JAYA</div>
                <div>JL.Wiyata No.81 RT23</div>
                <div>Kalimantan Timur</div>
            </td>
            <td>
                <div class="doc-title">SURAT PENGIRIMAN BARANG</div>
                <div class="center small">(Delivery Notes)</div>
                <table style="margin-top: 8px;">
                    <tr><td style="border:0;width:70px;">Nomor</td><td style="border:0;">: {{ $spb->no_spb }}</td></tr>
                    <tr><td style="border:0;">Tanggal</td><td style="border:0;">: {{ $tanggal?->translatedFormat('d F Y') ?? '-' }}</td></tr>
                    <tr><td style="border:0;">Halaman</td><td style="border:0;">: 1 of 1</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="ship" style="margin-top: 10px;">
        <tr>
            <td style="width: 48%;"></td>
            <td colspan="3"><strong>Ship To :</strong><br>{{ $spb->customer?->nama_customer ?? '-' }}<br>{{ $spb->site?->nama_site ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Nama Ekspedisi:</strong> {{ $spb->nama_ekspedisi ?? '-' }}</td>
            <td style="width: 70px;"><strong>ETD</strong></td><td>: {{ $spb->etd?->translatedFormat('d F Y') ?? '-' }}</td>
            <td><strong>ETA</strong> : {{ $spb->eta?->translatedFormat('d F Y') ?? '-' }}</td>
        </tr>
    </table>

    <table class="items" style="margin-top: 10px;">
        <thead>
            <tr>
                <th>NO CASE</th><th>BERAT</th><th>VOLUME</th><th>NO PO</th><th>INVOICE</th><th>NO GR</th><th>ITEM</th><th>NO MATERIAL</th><th>PENJELASAN</th><th>JUMLAH</th><th>SKU</th><th>DIMENSI</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($spb->items as $item)
                <tr>
                    <td></td>
                    <td class="right">{{ $item->berat ? number_format((float) $item->berat, 2, ',', '.') : '' }}</td>
                    <td class="right">{{ $item->volume ? number_format((float) $item->volume, 2, ',', '.') : '' }}</td>
                    <td>{{ $spb->no_referensi }}</td>
                    <td></td>
                    <td></td>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $item->part_no }}</td>
                    <td>{{ $item->deskripsi }}</td>
                    <td class="center">{{ number_format((float) $item->qty, 0, ',', '.') }}</td>
                    <td class="center">{{ $item->sku ?? 'PCS' }}</td>
                    <td>{{ $item->dimensi ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
