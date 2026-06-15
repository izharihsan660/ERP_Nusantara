<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $spb->no_spb }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1, h2, h3, p { margin: 0; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 16px; }
        .company { font-size: 18px; font-weight: 700; letter-spacing: .3px; }
        .muted { color: #4b5563; }
        .title { text-align: center; font-size: 16px; font-weight: 700; margin: 18px 0; text-transform: uppercase; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td { vertical-align: top; padding: 3px 0; }
        .label { width: 120px; color: #4b5563; }
        .items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .items th, .items td { border: 1px solid #d1d5db; padding: 6px; }
        .items th { background: #f3f4f6; text-align: left; }
        .text-right { text-align: right; }
        .signatures { width: 100%; margin-top: 42px; border-collapse: collapse; }
        .signatures td { width: 33.33%; text-align: center; vertical-align: bottom; }
        .sign-line { margin: 54px 22px 0; border-top: 1px solid #111827; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PT. Nusantara Abadi Jaya</div>
        <div class="muted">Distributor spare part & pallet</div>
        <div class="muted">Makassar, Sulawesi Selatan</div>
    </div>

    <div class="title">Surat Pengiriman Barang (Delivery Notes)</div>

    <table class="grid">
        <tr>
            <td class="label">No. SPB</td>
            <td>: {{ $spb->no_spb }}</td>
            <td class="label">Tanggal</td>
            <td>: {{ $spb->tgl_spb?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label">Kepada</td>
            <td>: {{ $spb->customer?->nama_customer }}</td>
            <td class="label">Referensi</td>
            <td>: {{ $spb->referensi_tipe?->value }} - {{ $spb->no_referensi }}</td>
        </tr>
        <tr>
            <td class="label">Site Tujuan</td>
            <td>: {{ $spb->site?->nama_site ?? '-' }}</td>
            <td class="label">Ekspedisi</td>
            <td>: {{ $spb->nama_ekspedisi }}</td>
        </tr>
        <tr>
            <td class="label">Alamat Site</td>
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
                <th>Deskripsi</th>
                <th class="text-right">Qty</th>
                <th>Satuan</th>
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
                    <td>{{ $item->satuan }}</td>
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
            <td><div class="sign-line">Penerima</div></td>
            <td><div class="sign-line">Ekspedisi</div></td>
        </tr>
    </table>
</body>
</html>
