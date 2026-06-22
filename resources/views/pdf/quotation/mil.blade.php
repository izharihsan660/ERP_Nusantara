@php
    $customer = $quotation->customer;
    $tanggal = $quotation->tgl_quotation ? \Carbon\Carbon::parse($quotation->tgl_quotation)->translatedFormat('d F Y') : '-';
    $subtotal = (float) $quotation->items->sum(fn ($item) => (float) $item->jumlah);
    $ppn = $subtotal * 0.11;
    $grandTotal = $subtotal + $ppn;
    $logoPath = public_path('images/logo-naj.png');
    $status = $quotation->status?->value ?? $quotation->status;
    $alamatLines = preg_split('/\r\n|\r|\n/', (string) ($customer?->alamat ?? ''));
    $perihalText = $perihal ?: ($quotation->items->pluck('deskripsi')->filter()->first() ?? 'barang yang dibutuhkan');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->no_quotation }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; line-height: 1.3; }
        table { border-collapse: collapse; width: 100%; }
        .kop td { vertical-align: top; }
        .company-name { font-size: 18px; font-weight: 700; }
        .company-address { font-size: 10px; line-height: 1.35; }
        .thick-line { border-top: 3px solid #000; margin-top: 8px; margin-bottom: 14px; }
        .title { text-align: center; font-size: 15px; font-weight: 700; text-decoration: underline; margin-bottom: 16px; }
        .info td { vertical-align: top; padding: 2px 0; }
        .label { width: 72px; }
        .separator { width: 10px; text-align: center; }
        .spacer { width: 36px; }
        .intro { margin-top: 14px; margin-bottom: 10px; }
        .items th, .items td { border: 1px solid #000; padding: 4px 5px; vertical-align: top; }
        .items th { text-align: center; font-weight: 700; }
        .center { text-align: center; }
        .right { text-align: right; }
        .summary { width: 285px; margin-left: auto; margin-top: 0; }
        .summary td { border: 1px solid #000; padding: 4px 6px; }
        .summary .label-total { font-weight: 700; }
        .terms { margin-top: 12px; }
        .terms td { padding: 2px 0; vertical-align: top; }
        .closing { margin-top: 14px; }
        .signature { margin-top: 18px; width: 260px; }
        .signature td { vertical-align: top; }
        .blue { color: #1a56db; }
    </style>
</head>
<body>
    <table class="kop">
        <tr>
            <td style="width:82px;">
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="width:70px; height:auto;" alt="NAJ">
                @else
                    <div style="font-size:22px; font-weight:900; border:3px solid #000; width:52px; height:52px; line-height:52px; text-align:center;">NAJ</div>
                @endif
            </td>
            <td>
                <div class="company-name">PT. NUSANTARA ABADI JAYA</div>
                <div class="company-address">
                    Distributor Spare Part &amp; Pallet<br>
                    Jl. Perintis Kemerdekaan KM. 16 No. 9, Makassar<br>
                    Telp. 0411-555777 | Email: admin@nusantaraabadijaya.com
                </div>
            </td>
        </tr>
    </table>
    <div class="thick-line"></div>

    <div class="title">PENAWARAN</div>

    <table class="info">
        <tr>
            <td class="label">Tanggal</td><td class="separator">:</td><td>{{ $tanggal }}</td>
            <td class="spacer"></td>
            <td class="label">Penawaran No</td><td class="separator">:</td><td>{{ $quotation->no_quotation }}</td>
        </tr>
        <tr>
            <td class="label">Customer</td><td class="separator">:</td><td>{{ $customer?->nama_customer ?? '-' }}</td>
            <td></td>
            <td class="label">Revisi</td><td class="separator">:</td><td>{{ $quotation->revisi ?? 0 }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td><td class="separator">:</td>
            <td>
                @foreach ($alamatLines as $line)
                    {{ $line }}@if (! $loop->last)<br>@endif
                @endforeach
            </td>
            <td></td>
            <td class="label">Masa berlaku</td><td class="separator">:</td><td>{{ $masa_berlaku ?? '-' }}</td>
        </tr>
    </table>

    <table class="info" style="margin-top:12px;">
        <tr>
            <td class="label">Kepada</td><td class="separator">:</td>
            <td>Pimpinan {{ $customer?->nama_customer ?? '-' }}</td>
        </tr>
        @if ($quotation->catatan)
            <tr>
                <td></td><td></td><td>{{ $quotation->catatan }}</td>
            </tr>
        @endif
    </table>

    <div class="intro">
        Sehubungan dengan permintaan <em>{{ $perihalText }}</em>, berikut kami sampaikan penawaran harga sebagai berikut:
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:28px;">NO</th>
                <th style="width:105px;">PART NUMBER</th>
                <th>DESKRIPSI</th>
                <th style="width:42px;">QTY</th>
                <th style="width:38px;">Sat</th>
                <th style="width:82px;">HARGA</th>
                <th style="width:86px;">TOTAL</th>
                <th style="width:74px;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $item->part_no }}</td>
                    <td>{{ $item->deskripsi }}</td>
                    <td class="center">{{ $item->qty }}</td>
                    <td class="center">{{ $item->satuan }}</td>
                    <td class="right">@rupiah($item->harga_satuan)</td>
                    <td class="right">@rupiah($item->jumlah)</td>
                    <td>{{ $item->status ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="label-total">SUBTOTAL</td>
            <td class="right">@rupiah($subtotal)</td>
        </tr>
        <tr>
            <td class="label-total">PPN 11%</td>
            <td class="right">@rupiah($ppn)</td>
        </tr>
        <tr>
            <td class="label-total">Grand Total</td>
            <td class="right">@rupiah($grandTotal)</td>
        </tr>
    </table>

    <table class="terms">
        <tr><td colspan="3"><strong>Ketentuan</strong></td></tr>
        <tr>
            <td style="width:95px;">Lokasi</td><td style="width:12px;">:</td><td>{{ $customer?->kota ?? '-' }}</td>
        </tr>
        <tr>
            <td>Ppn 11%</td><td>:</td><td>Harga <strong><em><u>Grand Total</u></em></strong> termasuk Ppn 11%</td>
        </tr>
        <tr>
            <td>Pembayaran</td><td>:</td><td>{{ $metode_pembayaran ?: '-' }}</td>
        </tr>
        <tr>
            <td></td><td></td><td>PT.Nusantara Abadi Jaya, Bank Mandiri, Rek 1700011777772</td>
        </tr>
    </table>

    <div class="closing">
        Kami berterima kasih atas kesempatan yang diberikan, besar harapan penawaran kami dapat diterima. Jika ada pertanyaan lebih lanjut, silahkan menghubungi kami.
    </div>

    <table class="signature">
        <tr><td>Hormat kami.</td></tr>
        <tr><td>PT.NUSANTARA ABADI JAYA</td></tr>
        <tr>
            <td style="padding-top:10px;">
                @if (isset($signaturePath) && $signaturePath)
                    <img src="{{ $signaturePath }}" style="max-width:160px; max-height:65px; display:block;" alt="TTD">
                @else
                    <div style="height:65px;"></div>
                @endif
                <strong>{{ $quotation->approvedBy?->name ?? 'Ratih Tirana' }}</strong><br>
                Manager<br>
                <span class="blue">{{ $quotation->approvedBy?->email ?? 'tiranaratih@nusantaraabadijaya.com' }}</span>
            </td>
        </tr>
    </table>

    @if ($status === 'APPROVED' && ($quotation->qr_token || (isset($qrCode) && $qrCode)))
        <div style="position:fixed; right:0; bottom:0; text-align:center; font-size:9px;">
            @if (isset($qrCode) && $qrCode)
                <img src="{{ $qrCode }}" style="width:75px; height:75px;" alt="QR">
            @else
                {!! QrCode::size(75)->generate(url('/verify/' . $quotation->qr_token)) !!}
            @endif
        </div>
    @endif
</body>
</html>
