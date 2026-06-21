<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
@page{margin:1.5cm}body{font-family:Arial,sans-serif;font-size:11px;color:#111}.title{text-align:center;font-size:18px;font-weight:bold;margin-bottom:10px}table{width:100%;border-collapse:collapse}td{border:1px solid #ccc;padding:6px 8px;vertical-align:top}.no-border td{border:0}.sign td{height:110px;text-align:center;vertical-align:bottom}
</style></head><body>
<div class="title">TANDA TERIMA</div>
<table><tr><td>No: {{ $invoice->no_dokumen }}</td><td>Tanggal: {{ $invoice->tgl_dokumen?->translatedFormat('d F Y') ?? '-' }}</td></tr><tr><td colspan="2">Telah diterima dari:<br><strong>PT. Nusantara Abadi Jaya</strong></td></tr><tr><td colspan="2"><strong>Dokumen:</strong><br>☐ Invoice/Nota No. {{ $invoice->no_dokumen }}<br>☐ Faktur Pajak No. {{ $invoice->no_faktur_pajak ?: '-' }}<br>☐ SPB No. {{ $invoice->spb?->no_spb ?? '-' }}</td></tr><tr><td colspan="2"><strong>Total Nilai:</strong> @rupiah($invoice->total_nilai)</td></tr></table>
<table class="sign" style="margin-top:16px"><tr><td>Penerima:<br>{{ $invoice->customer?->nama_customer ?? '-' }}<br><br>_______________<br>(tanda tangan)<br>Tanggal:</td><td>Pengirim:<br>NAJ<br><br>___________<br>(tanda tangan)<br>Tanggal:</td></tr></table>
</body></html>
