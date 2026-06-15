<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $permintaanDana->no_pd }}</title>
    <style>
        body { color: #0f172a; font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.45; }
        .header { border-bottom: 2px solid #0f172a; margin-bottom: 22px; padding-bottom: 12px; }
        .company { font-size: 18px; font-weight: bold; letter-spacing: .4px; }
        .muted { color: #475569; }
        .title { font-size: 20px; font-weight: bold; margin: 18px 0 8px; text-align: center; }
        .meta { margin-bottom: 18px; width: 100%; }
        .meta td { padding: 5px 0; vertical-align: top; }
        .box { border: 1px solid #cbd5e1; margin-top: 12px; padding: 12px; }
        .amount { font-size: 18px; font-weight: bold; }
        .signature { margin-top: 42px; width: 100%; }
        .signature td { text-align: center; width: 50%; }
        .signature .space { height: 62px; }
        .footer { bottom: 20px; color: #475569; font-size: 10px; position: fixed; right: 0; width: 170px; }
        .qr { margin-left: auto; width: 96px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">PT. Nusantara Abadi Jaya</div>
        <div class="muted">Makassar - Dokumen Permintaan Dana</div>
        <div class="muted">Jl. Nusantara Abadi Jaya, Makassar</div>
    </div>

    <div class="title">PERMINTAAN DANA</div>

    <table class="meta">
        <tr>
            <td width="140">No. PD</td>
            <td width="10">:</td>
            <td>{{ $permintaanDana->no_pd }}</td>
            <td width="140">Tanggal</td>
            <td width="10">:</td>
            <td>{{ $permintaanDana->tgl_pd?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Kategori</td>
            <td>:</td>
            <td>{{ $permintaanDana->kategori->label() }}</td>
            <td>Referensi Dokumen</td>
            <td>:</td>
            <td>{{ $permintaanDana->referensi_dokumen ?: '-' }}</td>
        </tr>
        <tr>
            <td>Diajukan oleh</td>
            <td>:</td>
            <td>{{ $permintaanDana->createdBy?->name ?: '-' }}</td>
            <td>Disetujui oleh</td>
            <td>:</td>
            <td>{{ $permintaanDana->approvedBy?->name ?: 'Manager' }}</td>
        </tr>
    </table>

    <div class="box">
        <div class="muted">Nominal</div>
        <div class="amount">Rp {{ number_format((float) $permintaanDana->nominal, 2, ',', '.') }}</div>
    </div>

    <div class="box">
        <strong>Keterangan</strong>
        <div>{{ $permintaanDana->keterangan }}</div>
    </div>

    <table class="signature">
        <tr>
            <td>Procurement,</td>
            <td>Manager,</td>
        </tr>
        <tr>
            <td class="space"></td>
            <td class="space"></td>
        </tr>
        <tr>
            <td>{{ $permintaanDana->createdBy?->name ?: 'Procurement' }}</td>
            <td>{{ $permintaanDana->approvedBy?->name ?: 'Manager' }}</td>
        </tr>
    </table>

    <div class="footer">
        <img class="qr" src="{{ $qrCode }}" alt="QR Verifikasi">
        <div>Scan untuk verifikasi dokumen.</div>
    </div>
</body>
</html>
