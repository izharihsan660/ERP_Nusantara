@php
    $subtotal = $invoice->total_nilai;
    $ppn = $invoice->ppn;
    $total = $invoice->grand_total;
    $metode = $invoice->metode_pembayaran;
    $metodeLabel = is_object($metode) && method_exists($metode, 'label') ? $metode->label() : ($metode?->value ?? $metode ?? '-');
@endphp
<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
@page{margin:1.5cm}body{font-family:Arial,sans-serif;font-size:11px;color:#111}table{width:100%;border-collapse:collapse}td,th{padding:4px 8px;vertical-align:top}.box td,.box th{border:1px solid #ccc}.company{font-size:13px;font-weight:bold}.title{font-size:18px;font-weight:bold;text-align:center}.items th{background:#f5f5f5;text-align:center;font-weight:bold}.right{text-align:right}.center{text-align:center}.summary{width:42%;margin-left:auto;margin-top:8px}.summary td{border:1px solid #ccc}.label{background:#f5f5f5;font-weight:bold}
</style></head><body>
<table class="box"><tr><td style="width:55%"><div class="company">PT. NUSANTARA ABADI JAYA</div><div>JL. Wiyata No. 81 RT 23</div><div>Kalimantan Timur</div></td><td><div class="title">INVOICE</div><table><tr><td style="border:0;width:70px">No</td><td style="border:0">: {{ $invoice->no_dokumen }}</td></tr><tr><td style="border:0">Tanggal</td><td style="border:0">: {{ $invoice->tgl_dokumen?->translatedFormat('d F Y') ?? '-' }}</td></tr></table></td></tr></table>
<table class="box" style="margin-top:10px"><tr><td><strong>Kepada:</strong><br>{{ $invoice->customer?->nama_customer ?? '-' }}<br>{{ $invoice->customer?->alamat ?? '-' }}<br>No. Faktur Pajak: {{ $invoice->no_faktur_pajak ?: '-' }}</td></tr></table>
<table class="box items" style="margin-top:10px"><thead><tr><th style="width:28px">NO</th><th>DESKRIPSI</th><th style="width:50px">QTY</th><th style="width:45px">SAT</th><th style="width:95px">HARGA</th><th style="width:105px">TOTAL</th></tr></thead><tbody>@foreach($items as $item)<tr><td class="center">{{ $loop->iteration }}</td><td>{{ $item['deskripsi'] }}</td><td class="center">{{ number_format((float) $item['qty'],0,',','.') }}</td><td class="center">{{ $item['satuan'] ?? 'PCS' }}</td><td class="right">@rupiah($item['harga_satuan'])</td><td class="right">@rupiah($item['jumlah'])</td></tr>@endforeach</tbody></table>
<table class="summary"><tr><td class="label">SUBTOTAL</td><td class="right">@rupiah($subtotal)</td></tr><tr><td class="label">PPN 11%</td><td class="right">@rupiah($ppn)</td></tr><tr><td class="label">TOTAL</td><td class="right"><strong>@rupiah($total)</strong></td></tr></table>
<table class="box" style="margin-top:12px"><tr><td>Metode Pembayaran: {{ $metodeLabel }}</td></tr><tr><td>Jatuh Tempo: {{ $invoice->tgl_jatuh_tempo?->translatedFormat('d F Y') ?? '-' }}</td></tr></table>
<table class="box" style="margin-top:10px"><tr><td><strong>Rekening:</strong><br>PT. Nusantara Abadi Jaya<br>Bank Mandiri No. 1700011777772</td></tr></table>
</body></html>
