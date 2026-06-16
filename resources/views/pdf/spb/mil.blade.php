<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $spb->no_spb }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #172554; }
        h1, h2, h3, p { margin: 0; }
        .header { border: 2px solid #1d4ed8; padding: 12px; margin-bottom: 16px; }
        .company { font-size: 17px; font-weight: 700; color: #1d4ed8; }
        .muted { color: #475569; }
        .title { text-align: center; font-size: 15px; font-weight: 700; margin: 18px 0; text-transform: uppercase; color: #1e3a8a; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td { vertical-align: top; padding: 3px 0; }
        .label { width: 120px; color: #475569; }
        .items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .items th, .items td { border: 1px solid #93c5fd; padding: 6px; }
        .items th { background: #dbeafe; color: #1e3a8a; text-align: left; }
        .text-right { text-align: right; }
        .signatures { width: 100%; margin-top: 42px; border-collapse: collapse; }
        .signatures td { width: 33.33%; text-align: center; vertical-align: bottom; }
        .sign-line { margin: 54px 22px 0; border-top: 1px solid #1d4ed8; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PT. Nusantara Abadi Jaya - Delivery Notes MIL</div>
        <div class="muted">Format pengiriman khusus customer MIL</div>
        <div class="muted">Makassar, Sulawesi Selatan</div>
    </div>

    <div class="title">Surat Pengiriman Barang (Delivery Notes)</div>

    <table class="grid">
        <tr>
            <td class="label">No. SPB</td>
            <td>: {{ $spb->no_spb }}</td>
            <td class="label">Tanggal Kirim</td>
            <td>: {{ $spb->tgl_spb?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Customer</td>
            <td>: {{ $spb->customer?->nama_customer }}</td>
            <td class="label">No. Referensi</td>
            <td>: {{ $spb->referensi_tipe?->value }} - {{ $spb->no_referensi }}</td>
        </tr>
        <tr>
            <td class="label">Tujuan</td>
            <td>: {{ $spb->site?->nama_site ?? '-' }}</td>
            <td class="label">Ekspedisi</td>
            <td>: {{ $spb->nama_ekspedisi }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td>: {{ $spb->site?->alamat ?? $spb->customer?->alamat ?? '-' }}</td>
            <td class="label">ETD / ETA</td>
            <td>: {{ $spb->etd?->format('d/m/Y') ?? '-' }} / {{ $spb->eta?->format('d/m/Y') ?? '-' }}</td>
        </tr>
    </table>

    @if ($spb->catatan)
        <p><strong>Catatan:</strong> {{ $spb->catatan }}</p>
    @endif

    <table class="items">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Part No</th>
                <th>Deskripsi Barang</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Berat</th>
                <th class="text-right">Volume</th>
                <th>Dimensi</th>
                <th>SKU</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($spb->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->part_no }}</td>
                    <td>{{ $item->deskripsi }}</td>
                    <td class="text-right">{{ number_format((int) $item->qty, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format((float) $item->berat, 2, ',', '.') }} kg</td>
                    <td class="text-right">{{ number_format((float) $item->volume, 2, ',', '.') }} m&sup3;</td>
                    <td>{{ $item->dimensi ?? '-' }}</td>
                    <td>{{ $item->sku ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td><div class="sign-line">Pengirim</div></td>
            <td><div class="sign-line">Penerima MIL</div></td>
            <td><div class="sign-line">Ekspedisi</div></td>
        </tr>
    </table>
</body>
</html>
