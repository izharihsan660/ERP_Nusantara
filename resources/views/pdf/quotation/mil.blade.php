@php
    $customer = $quotation->customer;
    $salesOrder = $quotation->salesOrder;
    $site = $salesOrder?->site ?? null;
    $tanggal = $quotation->tgl_quotation ? \Carbon\Carbon::parse($quotation->tgl_quotation) : null;
    $masaBerlaku = $tanggal?->copy()->addMonths(6);
    $subtotal = (float) $quotation->items->sum(fn ($item) => (float) $item->jumlah);
    $ppn = $subtotal * 0.11;
    $grandTotal = $subtotal + $ppn;
    $metodePembayaran = $salesOrder?->metode_pembayaran;
    $metodeLabel = is_object($metodePembayaran) && method_exists($metodePembayaran, 'label') ? $metodePembayaran->label() : ($metodePembayaran?->value ?? $metodePembayaran ?? 'Tempo N30/CASH');
    $partType = $quotation->items->pluck('deskripsi')->filter()->first() ?? 'part';
    $status = $quotation->status?->value ?? $quotation->status;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #111; }
        .title { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 12px; letter-spacing: .5px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 4px 8px; vertical-align: top; }
        .box { border: 1px solid #ccc; margin-bottom: 10px; }
        .box td { border: 0; }
        .label { width: 86px; font-weight: bold; }
        .sep { width: 8px; }
        .items th, .items td { border: 1px solid #ccc; }
        .items th { background: #f5f5f5; font-weight: bold; text-align: center; }
        .center { text-align: center; }
        .right { text-align: right; }
        .summary { width: 42%; margin-left: auto; margin-top: 8px; }
        .summary td { border: 1px solid #ccc; }
        .summary .label-total { background: #f5f5f5; font-weight: bold; }
        .terms td { padding: 3px 8px; }
        .qr { position: fixed; right: 0; bottom: 0; text-align: center; font-size: 9px; }
    </style>
</head>
<body>
    <div class="title">PENAWARAN</div>

    <table class="box">
        <tr>
            <td class="label">Tanggal</td><td class="sep">:</td><td>{{ $tanggal?->translatedFormat('d F Y') ?? '-' }}</td>
            <td class="label">Penawaran No</td><td class="sep">:</td><td>{{ $quotation->no_quotation }}</td>
        </tr>
        <tr>
            <td class="label">Customer</td><td>:</td><td>{{ $customer?->nama_customer ?? '-' }}</td>
            <td class="label">Revisi</td><td>:</td><td>{{ $quotation->revisi ?? 0 }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td><td>:</td><td>{{ trim(($customer?->alamat ?? '').' '.($customer?->kota ?? '')) ?: '-' }}</td>
            <td class="label">Masa berlaku</td><td>:</td><td>{{ $masaBerlaku?->translatedFormat('d F Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kepada</td><td>:</td><td>{{ $customer?->pic_name ?? '-' }}</td>
            <td></td><td></td><td></td>
        </tr>
        <tr>
            <td class="label">Site</td><td>:</td><td colspan="4">{{ $site?->nama_site ?? '-' }}</td>
        </tr>
    </table>

    <div class="box" style="padding: 8px; line-height: 1.5;">
        <div>Dengan Hormat,</div>
        <div>Sehubungan dengan permintaan {{ strtoupper($partType) }}, berikut kami kirimkan daftar harga dan penawarannya :</div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 28px;">NO</th>
                <th>PART NUMBER (ICHIBAN)</th>
                <th>PART NUMBER (OEM)</th>
                <th>DESKRIPSI</th>
                <th style="width: 42px;">QTY</th>
                <th style="width: 42px;">Sat</th>
                <th style="width: 82px;">HARGA</th>
                <th style="width: 88px;">TOTAL</th>
                <th style="width: 58px;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $item->part_no_ichiban ?? $item->katalog?->part_no_ichiban ?? $item->part_no }}</td>
                    <td>{{ $item->part_no_oem ?? $item->katalog?->part_no_oem ?? '' }}</td>
                    <td>{{ $item->deskripsi }}</td>
                    <td class="center">{{ number_format((float) $item->qty, 0, ',', '.') }}</td>
                    <td class="center">{{ $item->satuan }}</td>
                    <td class="right">@rupiah($item->harga_satuan)</td>
                    <td class="right">@rupiah($item->jumlah)</td>
                    <td class="center">{{ $item->status ?? 'PO' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr><td class="label-total">SUBTOTAL</td><td class="right">@rupiah($subtotal)</td></tr>
        <tr><td class="label-total">PPN 11%</td><td class="right">@rupiah($ppn)</td></tr>
        <tr><td class="label-total">Grand Total</td><td class="right"><strong>@rupiah($grandTotal)</strong></td></tr>
    </table>

    <table class="box terms" style="margin-top: 12px;">
        <tr><td colspan="3"><strong>Ketentuan:</strong></td></tr>
        <tr><td style="width: 90px;">Lokasi</td><td style="width: 8px;">:</td><td>{{ $site?->nama_site ?? $customer?->kota ?? 'Balikpapan' }}</td></tr>
        <tr><td>Ppn 11%</td><td>:</td><td>Harga Grand Total termasuk Ppn 11%</td></tr>
        <tr><td>Pembayaran</td><td>:</td><td>{{ $metodeLabel }}</td></tr>
        <tr><td colspan="3">PT.Nusantara Abadi Jaya,<br>Bank Mandiri, Rek 1700011777772</td></tr>
    </table>

    @if (($quotation->qr_token || isset($qrCode)) && $status === 'APPROVED')
        <div class="qr">
            @isset($qrCode)
                <img src="{{ $qrCode }}" style="width: 80px; height: 80px;" alt="QR Code">
            @else
                {!! QrCode::size(80)->generate(url('/verify/' . $quotation->qr_token)) !!}
            @endisset
            <div>Approved: {{ $quotation->approvedBy?->name ?? '-' }}</div>
        </div>
    @endif
</body>
</html>
