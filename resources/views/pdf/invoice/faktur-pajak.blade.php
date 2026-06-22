@php
    $dpp = collect($items)->sum(fn ($item) => (float) $item['jumlah']);
    $ppn = $dpp * 0.11;
    $total = $dpp + $ppn;
@endphp
<!doctype html><html lang="id"><head><meta charset="utf-8"><style>
@page{margin:1.5cm}body{font-family:Arial,sans-serif;font-size:11px;color:#111}.title{text-align:center;font-size:18px;font-weight:bold;margin-bottom:10px}table{width:100%;border-collapse:collapse}td,th{padding:4px 8px;vertical-align:top}.box td,.box th{border:1px solid #ccc}.items th{background:#f5f5f5;text-align:center}.right{text-align:right}.center{text-align:center}.summary{width:42%;margin-left:auto;margin-top:8px}.summary td{border:1px solid #ccc}.label{background:#f5f5f5;font-weight:bold}
</style></head><body>
<div class="title">FAKTUR PAJAK</div><table class="box"><tr><td>No. Faktur: {{ $invoice->no_faktur_pajak ?: '-' }}</td><td>Tanggal: {{ $invoice->tgl_dokumen?->translatedFormat('d F Y') ?? '-' }}</td></tr></table>
<table class="box" style="margin-top:10px"><tr><td style="width:50%"><strong>Penjual:</strong><br>PT. Nusantara Abadi Jaya<br>NPWP: {{ config('app.npwp_naj', '-') }}<br>JL. Wiyata No. 81 RT 23, Kalimantan Timur</td><td><strong>Pembeli:</strong><br>{{ $invoice->customer?->nama_customer ?? '-' }}<br>NPWP: {{ $invoice->customer?->npwp ?? '-' }}<br>{{ $invoice->customer?->alamat ?? '-' }}</td></tr></table>
<table class="box items" style="margin-top:10px"><thead><tr><th style="width:28px">NO</th><th>NAMA BARANG</th><th style="width:50px">QTY</th><th style="width:95px">HARGA</th><th style="width:95px">DPP</th><th style="width:95px">PPN</th></tr></thead><tbody>@foreach($items as $item)<tr><td class="center">{{ $loop->iteration }}</td><td>{{ $item['deskripsi'] }}</td><td class="center">{{ number_format((float) $item['qty'],0,',','.') }}</td><td class="right">@rupiah($item['harga_satuan'])</td><td class="right">@rupiah($item['jumlah'])</td><td class="right">@rupiah(((float) $item['jumlah']) * 0.11)</td></tr>@endforeach</tbody></table>
<table class="summary"><tr><td class="label">DPP Total</td><td class="right">@rupiah($dpp)</td></tr><tr><td class="label">PPN 11%</td><td class="right">@rupiah($ppn)</td></tr><tr><td class="label">Total</td><td class="right"><strong>@rupiah($total)</strong></td></tr></table>
</body></html>
